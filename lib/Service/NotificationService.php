<?php
namespace OCA\KursUmstufung\Service;

use OCA\KursUmstufung\Db\Request;
use OCP\IGroupManager;
use OCP\Notification\IManager;
use Psr\Log\LoggerInterface;

/**
 * Erzeugt Nextcloud-Benachrichtigungen bei Workflow-Ereignissen.
 * Schließt die Lücke zwischen CHANGELOG-Versprechen und Realität.
 * Fehler beim Benachrichtigen dürfen den fachlichen Vorgang nie abbrechen,
 * werden aber strukturiert geloggt (kein Silent Failure).
 */
class NotificationService {
    public const APP_ID = 'kursumstufung';
    public const SUBJECT_SUBMITTED = 'requests_submitted';
    public const SUBJECT_DECIDED = 'request_decided';

    private IManager $manager;
    private IGroupManager $groupManager;
    private ConfigService $configService;
    private LoggerInterface $logger;

    public function __construct(
        IManager $manager,
        IGroupManager $groupManager,
        ConfigService $configService,
        LoggerInterface $logger
    ) {
        $this->manager = $manager;
        $this->groupManager = $groupManager;
        $this->configService = $configService;
        $this->logger = $logger;
    }

    /**
     * Informiert die Mitglieder der Schulleitungs-Gruppe über neu
     * eingereichte Anträge.
     */
    public function notifySubmitted(string $actorUserId, int $count): void {
        if ($count < 1) {
            return;
        }
        $group = $this->groupManager->get($this->configService->getAdminGroup());
        if ($group === null) {
            return;
        }
        try {
            foreach ($group->getUsers() as $user) {
                if ($user->getUID() === $actorUserId) {
                    continue;
                }
                $notification = $this->manager->createNotification();
                $notification->setApp(self::APP_ID)
                    ->setUser($user->getUID())
                    ->setDateTime(new \DateTime())
                    ->setObject('requests', $actorUserId)
                    ->setSubject(self::SUBJECT_SUBMITTED, [
                        'actor' => $actorUserId,
                        'count' => $count,
                    ]);
                $this->manager->notify($notification);
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Konnte Schulleitung nicht benachrichtigen: ' . $e->getMessage(), [
                'app' => self::APP_ID,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Informiert die einreichende Lehrkraft über eine Entscheidung.
     */
    public function notifyDecided(Request $request, string $decision): void {
        try {
            $notification = $this->manager->createNotification();
            $notification->setApp(self::APP_ID)
                ->setUser($request->getUserId())
                ->setDateTime(new \DateTime())
                ->setObject('request', (string)$request->getId())
                ->setSubject(self::SUBJECT_DECIDED, [
                    'student' => (string)$request->getStudentName(),
                    'decision' => $decision,
                ]);
            $this->manager->notify($notification);
        } catch (\Throwable $e) {
            $this->logger->warning('Konnte Lehrkraft nicht benachrichtigen: ' . $e->getMessage(), [
                'app' => self::APP_ID,
                'exception' => $e,
            ]);
        }
    }
}
