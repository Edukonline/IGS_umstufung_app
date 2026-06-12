<?php
namespace OCA\KursUmstufung\Controller;

use OCA\KursUmstufung\Exception\ForbiddenException;
use OCA\KursUmstufung\Exception\ValidationException;
use OCA\KursUmstufung\Service\AuthorizationService;
use OCA\KursUmstufung\Service\RequestService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use Psr\Log\LoggerInterface;

class RequestController extends Controller {
    private RequestService $service;
    private AuthorizationService $auth;
    private LoggerInterface $logger;

    public function __construct(
        string $appName,
        IRequest $request,
        RequestService $service,
        AuthorizationService $auth,
        LoggerInterface $logger
    ) {
        parent::__construct($appName, $request);
        $this->service = $service;
        $this->auth = $auth;
        $this->logger = $logger;
    }

    /**
     * @NoAdminRequired
     */
    public function index(?string $schoolYear = null): DataResponse {
        return $this->guard(function () use ($schoolYear) {
            $userId = $this->auth->getUserId();
            $isSchulleitung = $this->auth->isSchulleitung($userId);
            $requests = $isSchulleitung
                ? $this->service->findAllSubmitted($schoolYear)
                : $this->service->findAllByUser($userId, $schoolYear);

            return new DataResponse([
                'isSchulleitung' => $isSchulleitung,
                'requests' => $requests,
            ]);
        });
    }

    /**
     * @NoAdminRequired
     */
    public function create(): DataResponse {
        return $this->guard(function () {
            $request = $this->service->create($this->auth->getUserId(), $this->payload());
            return new DataResponse($request);
        });
    }

    /**
     * @NoAdminRequired
     */
    public function update(int $id): DataResponse {
        return $this->guard(function () use ($id) {
            $request = $this->service->update($id, $this->auth->getUserId(), $this->payload());
            return new DataResponse($request);
        });
    }

    /**
     * @NoAdminRequired
     */
    public function destroy(int $id): DataResponse {
        return $this->guard(function () use ($id) {
            $this->service->delete($id, $this->auth->getUserId());
            return new DataResponse(['status' => 'success']);
        });
    }

    /**
     * @NoAdminRequired
     */
    public function submitAll(): DataResponse {
        return $this->guard(function () {
            $count = $this->service->submitAllDraftsForUser($this->auth->getUserId());
            return new DataResponse(['status' => 'success', 'submitted' => $count]);
        });
    }

    /**
     * Genehmigt oder lehnt einen Antrag ab. Nur für die Schulleitung.
     * @NoAdminRequired
     */
    public function decide(int $id, string $decision): DataResponse {
        return $this->guard(function () use ($id, $decision) {
            if (!$this->auth->isSchulleitung()) {
                throw new ForbiddenException('Nur die Schulleitung darf entscheiden.');
            }
            $reason = (string)$this->request->getParam('decisionReason', '');
            $request = $this->service->decide($id, $this->auth->getUserId(), $decision, $reason);
            return new DataResponse($request);
        });
    }

    /* -------------------------------------------------- Helpers */

    /** @return array<string,mixed> */
    private function payload(): array {
        return [
            'studentName' => $this->request->getParam('studentName', ''),
            'class' => $this->request->getParam('class', ''),
            'subject' => $this->request->getParam('subject', ''),
            'oldLevel' => $this->request->getParam('oldLevel', ''),
            'newLevel' => $this->request->getParam('newLevel', ''),
            'reason' => $this->request->getParam('reason', ''),
        ];
    }

    /**
     * Einheitliches Error-Handling: fachliche Fehler werden auf passende
     * HTTP-Codes gemappt, unerwartete Fehler serverseitig geloggt und dem
     * Client nur generisch gemeldet (kein Leak von internen Details).
     */
    private function guard(callable $fn): DataResponse {
        try {
            return $fn();
        } catch (ValidationException $e) {
            return new DataResponse(['error' => $e->getMessage()], Http::STATUS_BAD_REQUEST);
        } catch (ForbiddenException $e) {
            return new DataResponse(['error' => $e->getMessage()], Http::STATUS_FORBIDDEN);
        } catch (DoesNotExistException $e) {
            return new DataResponse(['error' => 'Antrag nicht gefunden.'], Http::STATUS_NOT_FOUND);
        } catch (\Throwable $e) {
            $this->logger->error('Unerwarteter Fehler im RequestController: ' . $e->getMessage(), [
                'app' => 'kursumstufung',
                'exception' => $e,
            ]);
            return new DataResponse(['error' => 'Ein interner Fehler ist aufgetreten.'], Http::STATUS_INTERNAL_SERVER_ERROR);
        }
    }
}
