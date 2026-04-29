<?php
namespace OCA\KursUmstufung\Service;

use OCA\KursUmstufung\Db\Request;
use OCA\KursUmstufung\Db\RequestMapper;
use OCP\IUserManager;

class RequestService {
    private RequestMapper $mapper;
    private IUserManager $userManager;

    public function __construct(RequestMapper $mapper, IUserManager $userManager) {
        $this->mapper = $mapper;
        $this->userManager = $userManager;
    }

    private function populateUserNames(array $requests): array {
        foreach ($requests as $request) {
            $user = $this->userManager->get($request->getUserId());
            if ($user) {
                $request->setUserName($user->getDisplayName());
            } else {
                $request->setUserName($request->getUserId());
            }
        }
        return $requests;
    }

    public function findAllByUser(string $userId): array {
        return $this->populateUserNames($this->mapper->findAllByUser($userId));
    }

    public function findAllSubmitted(): array {
        return $this->populateUserNames($this->mapper->findAllSubmitted());
    }

    public function find(int $id): Request {
        $request = $this->mapper->findById($id);
        $user = $this->userManager->get($request->getUserId());
        if ($user) {
            $request->setUserName($user->getDisplayName());
        }
        return $request;
    }

    public function create(string $userId, string $studentName, string $class, string $subject, string $oldLevel, string $newLevel, string $reason): Request {
        try {
            $request = new Request();
            $request->setUserId($userId);
            $request->setStudentName($studentName);
            $request->setStudentClass($class);
            $request->setSubject($subject);
            $request->setOldLevel($oldLevel);
            $request->setNewLevel($newLevel);
            $request->setReason($reason);
            $request->setStatus('draft');
            $request->setCreatedAt(new \DateTime());
            $request->setUpdatedAt(new \DateTime());

            return $this->mapper->insert($request);
        } catch (\Exception $e) {
            throw new \Exception("Datenbank-Fehler: " . $e->getMessage());
        }
    }

    public function update(int $id, string $userId, string $studentName, string $class, string $subject, string $oldLevel, string $newLevel, string $reason): Request {
        $request = $this->mapper->findById($id);
        
        if ($request->getUserId() !== $userId || $request->getStatus() !== 'draft') {
            throw new \Exception("Not allowed to edit this request");
        }

        $request->setStudentName($studentName);
        $request->setStudentClass($class);
        $request->setSubject($subject);
        $request->setOldLevel($oldLevel);
        $request->setNewLevel($newLevel);
        $request->setReason($reason);
        $request->setUpdatedAt(new \DateTime());
        
        $this->mapper->update($request);
        return $request;
    }

    public function delete(int $id, string $userId): Request {
        $request = $this->mapper->findById($id);
        
        if ($request->getUserId() !== $userId || $request->getStatus() !== 'draft') {
            throw new \Exception("Not allowed to delete this request");
        }

        return $this->mapper->delete($request);
    }

    public function submitAllDraftsForUser(string $userId): void {
        $this->mapper->submitAllDraftsForUser($userId);
    }
}
