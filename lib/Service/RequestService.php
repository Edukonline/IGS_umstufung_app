<?php
namespace OCA\UmstufungMNS\Service;

use OCA\UmstufungMNS\Db\Request;
use OCA\UmstufungMNS\Db\RequestMapper;

class RequestService {
    private RequestMapper $mapper;

    public function __construct(RequestMapper $mapper) {
        $this->mapper = $mapper;
    }

    public function findAllByUser(string $userId): array {
        return $this->mapper->findAllByUser($userId);
    }

    public function findAllSubmitted(): array {
        return $this->mapper->findAllSubmitted();
    }

    public function find(int $id): Request {
        return $this->mapper->findById($id);
    }

    public function create(string $userId, string $studentName, string $subject, string $oldLevel, string $newLevel, string $reason): Request {
        try {
            $request = new Request();
            $request->setUserId($userId);
            $request->setStudentName($studentName);
            $request->setSubject($subject);
            $request->setOldLevel($oldLevel);
            $request->setNewLevel($newLevel);
            $request->setReason($reason);
            $request->setStatus('draft');
            $request->setCreatedAt(time());

            return $this->mapper->insert($request);
        } catch (\Exception $e) {
            // Wir werfen den Fehler mit mehr Details neu, damit er im Controller ankommt
            throw new \Exception("Datenbank-Fehler: " . $e->getMessage() . " (Prüfe ob Tabelle 'umstufung_mns_requests' existiert)");
        }
    }

    public function update(int $id, string $userId, string $studentName, string $subject, string $oldLevel, string $newLevel, string $reason): Request {
        $request = $this->mapper->findById($id);
        
        // Security check: Only allow editing drafts from the same user
        if ($request->getUserId() !== $userId || $request->getStatus() !== 'draft') {
            throw new \Exception("Not allowed to edit this request");
        }

        $request->setStudentName($studentName);
        $request->setSubject($subject);
        $request->setOldLevel($oldLevel);
        $request->setNewLevel($newLevel);
        $request->setReason($reason);
        
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
