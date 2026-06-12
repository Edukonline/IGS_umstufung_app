<?php
namespace OCA\KursUmstufung\Service;

use OCA\KursUmstufung\Constants\CourseLevel;
use OCA\KursUmstufung\Constants\RequestStatus;
use OCA\KursUmstufung\Db\Request;
use OCA\KursUmstufung\Db\RequestMapper;
use OCA\KursUmstufung\Exception\ForbiddenException;
use OCA\KursUmstufung\Exception\ValidationException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUserManager;

class RequestService {
    private const MAX_NAME = 255;
    private const MAX_CLASS = 16;
    private const MAX_SUBJECT = 64;
    private const MAX_REASON = 2000;
    private const MAX_DECISION_REASON = 2000;

    private RequestMapper $mapper;
    private IUserManager $userManager;
    private ConfigService $configService;
    private NotificationService $notificationService;

    public function __construct(
        RequestMapper $mapper,
        IUserManager $userManager,
        ConfigService $configService,
        NotificationService $notificationService
    ) {
        $this->mapper = $mapper;
        $this->userManager = $userManager;
        $this->configService = $configService;
        $this->notificationService = $notificationService;
    }

    /* -------------------------------------------------- Reads */

    public function findAllByUser(string $userId, ?string $schoolYear = null): array {
        return $this->populateNames($this->mapper->findAllByUser($userId, $schoolYear));
    }

    public function findAllSubmitted(?string $schoolYear = null): array {
        return $this->populateNames($this->mapper->findAllSubmitted($schoolYear));
    }

    /* -------------------------------------------------- Writes */

    public function create(string $userId, array $data): Request {
        $clean = $this->validate($data);

        $now = new \DateTime();
        $request = new Request();
        $request->setUserId($userId);
        $request->setStudentName($clean['studentName']);
        $request->setStudentClass($clean['class']);
        $request->setSubject($clean['subject']);
        $request->setOldLevel($clean['oldLevel']);
        $request->setNewLevel($clean['newLevel']);
        $request->setReason($clean['reason']);
        $request->setStatus(RequestStatus::DRAFT);
        $request->setSchoolYear($this->schoolYearFor($now));
        $request->setCreatedAt($now);
        $request->setUpdatedAt($now);

        return $this->withNames($this->mapper->insert($request));
    }

    public function update(int $id, string $userId, array $data): Request {
        $request = $this->getOwnedDraft($id, $userId);
        $clean = $this->validate($data);

        $request->setStudentName($clean['studentName']);
        $request->setStudentClass($clean['class']);
        $request->setSubject($clean['subject']);
        $request->setOldLevel($clean['oldLevel']);
        $request->setNewLevel($clean['newLevel']);
        $request->setReason($clean['reason']);
        $request->setUpdatedAt(new \DateTime());

        return $this->withNames($this->mapper->update($request));
    }

    public function delete(int $id, string $userId): void {
        $request = $this->getOwnedDraft($id, $userId);
        $this->mapper->delete($request);
    }

    public function submitAllDraftsForUser(string $userId): int {
        $count = $this->mapper->submitAllDraftsForUser($userId);
        if ($count > 0) {
            $this->notificationService->notifySubmitted($userId, $count);
        }
        return $count;
    }

    /**
     * Genehmigt oder lehnt einen eingereichten Antrag ab (nur Schulleitung).
     */
    public function decide(int $id, string $deciderUserId, string $decision, string $reason = ''): Request {
        if (!in_array($decision, RequestStatus::FINAL, true)) {
            throw new ValidationException('Ungültige Entscheidung.');
        }
        try {
            $request = $this->mapper->findById($id);
        } catch (DoesNotExistException $e) {
            throw new DoesNotExistException('Antrag nicht gefunden.');
        }
        if (!in_array($request->getStatus(), RequestStatus::DECIDABLE, true)) {
            throw new ForbiddenException('Über diesen Antrag kann nicht entschieden werden.');
        }

        $request->setStatus($decision);
        $request->setDecidedBy($deciderUserId);
        $request->setDecisionReason(mb_substr(trim($reason), 0, self::MAX_DECISION_REASON));
        $request->setUpdatedAt(new \DateTime());
        $saved = $this->mapper->update($request);

        $this->notificationService->notifyDecided($saved, $decision);
        return $this->withNames($saved);
    }

    /* -------------------------------------------------- Helpers */

    private function getOwnedDraft(int $id, string $userId): Request {
        try {
            $request = $this->mapper->findById($id);
        } catch (DoesNotExistException $e) {
            throw new DoesNotExistException('Antrag nicht gefunden.');
        }
        if ($request->getUserId() !== $userId) {
            throw new ForbiddenException('Kein Zugriff auf diesen Antrag.');
        }
        if ($request->getStatus() !== RequestStatus::DRAFT) {
            throw new ForbiddenException('Nur Entwürfe können bearbeitet werden.');
        }
        return $request;
    }

    /**
     * Validiert die Eingaben an der Trust Boundary und liefert bereinigte Werte.
     * @throws ValidationException
     */
    private function validate(array $data): array {
        $studentName = trim((string)($data['studentName'] ?? ''));
        $class = trim((string)($data['class'] ?? ''));
        $subject = trim((string)($data['subject'] ?? ''));
        $oldLevel = trim((string)($data['oldLevel'] ?? ''));
        $newLevel = trim((string)($data['newLevel'] ?? ''));
        $reason = trim((string)($data['reason'] ?? ''));

        if ($studentName === '') {
            throw new ValidationException('Der Name der Schülerin/des Schülers fehlt.');
        }
        if (mb_strlen($studentName) > self::MAX_NAME) {
            throw new ValidationException('Der Name ist zu lang.');
        }
        if ($class === '' || mb_strlen($class) > self::MAX_CLASS || !in_array($class, $this->configService->getClasses(), true)) {
            throw new ValidationException('Bitte eine gültige Klasse wählen.');
        }
        if ($subject === '' || mb_strlen($subject) > self::MAX_SUBJECT || !in_array($subject, $this->configService->getSubjects(), true)) {
            throw new ValidationException('Bitte ein gültiges Fach wählen.');
        }
        if (!CourseLevel::isValid($oldLevel) || !CourseLevel::isValid($newLevel)) {
            throw new ValidationException('Ungültiges Kurs-Niveau.');
        }
        if ($oldLevel === $newLevel) {
            throw new ValidationException('Von- und Nach-Niveau dürfen nicht identisch sein.');
        }
        if (mb_strlen($reason) > self::MAX_REASON) {
            throw new ValidationException('Die Begründung ist zu lang.');
        }

        return [
            'studentName' => $studentName,
            'class' => $class,
            'subject' => $subject,
            'oldLevel' => $oldLevel,
            'newLevel' => $newLevel,
            'reason' => $reason,
        ];
    }

    /**
     * Ergänzt Anzeigenamen (Lehrkraft + Entscheider) ohne N+1:
     * jede UID wird nur einmal aufgelöst.
     * @param Request[] $requests
     * @return Request[]
     */
    private function populateNames(array $requests): array {
        $cache = [];
        $resolve = function (?string $uid) use (&$cache): ?string {
            if ($uid === null || $uid === '') {
                return null;
            }
            if (!array_key_exists($uid, $cache)) {
                $user = $this->userManager->get($uid);
                $cache[$uid] = $user !== null ? $user->getDisplayName() : $uid;
            }
            return $cache[$uid];
        };

        foreach ($requests as $request) {
            $request->setUserName($resolve($request->getUserId()));
            $request->setDecidedByName($resolve($request->getDecidedBy()));
        }
        return $requests;
    }

    private function withNames(Request $request): Request {
        return $this->populateNames([$request])[0];
    }

    /**
     * Deutsches Schuljahr (1. August – 31. Juli) im Format "2025/2026".
     */
    private function schoolYearFor(\DateTimeInterface $date): string {
        $year = (int)$date->format('Y');
        $month = (int)$date->format('n');
        $start = $month >= 8 ? $year : $year - 1;
        return $start . '/' . ($start + 1);
    }
}
