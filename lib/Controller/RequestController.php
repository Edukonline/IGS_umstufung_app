<?php
namespace OCA\UmstufungMNS\Controller;

use OCA\UmstufungMNS\Service\RequestService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class RequestController extends Controller {
    private $service;

    public function __construct($AppName, IRequest $request, RequestService $service) {
        parent::__construct($AppName, $request);
        $this->service = $service;
    }

    /**
     * @NoAdminRequired
     */
    public function test() {
        return new DataResponse(['status' => 'OK', 'message' => 'API is reachable']);
    }

    private function getUserId() {
        $userSession = \OC::$server->getUserSession();
        $user = $userSession->getUser();
        return $user ? $user->getUID() : '';
    }

    private function isSchulleitung() {
        $userId = $this->getUserId();
        if (empty($userId)) {
            return false;
        }
        $groupManager = \OC::$server->getGroupManager();
        return $groupManager->isAdmin($userId) || $groupManager->isInGroup($userId, 'schulleitung');
    }

    /**
     * @NoAdminRequired
     */
    public function index() {
        try {
            $userId = $this->getUserId();
            if ($this->isSchulleitung()) {
                $requests = $this->service->findAllSubmitted();
            } else {
                $requests = $this->service->findAllByUser($userId);
            }
            return new DataResponse([
                'isSchulleitung' => $this->isSchulleitung(),
                'requests' => $requests
            ]);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function create() {
        $userId = $this->getUserId();
        $studentName = $this->request->getParam('studentName', '');
        $subject = $this->request->getParam('subject', '');
        $oldLevel = $this->request->getParam('oldLevel', '');
        $newLevel = $this->request->getParam('newLevel', '');
        $reason = $this->request->getParam('reason', '');

        try {
            $request = $this->service->create($userId, $studentName, $subject, $oldLevel, $newLevel, $reason);
            return new DataResponse($request);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function update($id) {
        $userId = $this->getUserId();
        $studentName = $this->request->getParam('studentName', '');
        $subject = $this->request->getParam('subject', '');
        $oldLevel = $this->request->getParam('oldLevel', '');
        $newLevel = $this->request->getParam('newLevel', '');
        $reason = $this->request->getParam('reason', '');

        try {
            // Sicherstellen, dass ID eine Zahl ist
            $requestId = (int)$id;
            $request = $this->service->update($requestId, $userId, $studentName, $subject, $oldLevel, $newLevel, $reason);
            return new DataResponse($request);
        } catch (\Exception $e) {
            return new DataResponse([
                'error' => 'Fehler beim Aktualisieren: ' . $e->getMessage(),
                'id' => $id,
                'userId' => $userId
            ], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function destroy($id) {
        $userId = $this->getUserId();
        try {
            $this->service->delete($id, $userId);
            return new DataResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function submitAll() {
        $userId = $this->getUserId();
        try {
            $this->service->submitAllDraftsForUser($userId);
            return new DataResponse(['status' => 'success']);
        } catch (\Exception $e) {
            return new DataResponse(['error' => $e->getMessage()], 500);
        }
    }
}
