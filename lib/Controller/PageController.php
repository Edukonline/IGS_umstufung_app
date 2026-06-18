<?php
namespace OCA\KursUmstufung\Controller;

use OCA\KursUmstufung\Constants\CourseLevel;
use OCA\KursUmstufung\Service\AuthorizationService;
use OCA\KursUmstufung\Service\ConfigService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IRequest;

class PageController extends Controller {
    private IInitialState $initialState;
    private AuthorizationService $auth;
    private ConfigService $configService;

    public function __construct(
        string $appName,
        IRequest $request,
        IInitialState $initialState,
        AuthorizationService $auth,
        ConfigService $configService
    ) {
        parent::__construct($appName, $request);
        $this->initialState = $initialState;
        $this->auth = $auth;
        $this->configService = $configService;
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function index(): TemplateResponse {
        // Source of Truth fürs Frontend serverseitig bereitstellen, statt
        // Rolle/Fächer/Klassen im Client-Bundle einzubetonieren.
        $this->initialState->provideInitialState('isSchulleitung', $this->auth->isSchulleitung());
        $this->initialState->provideInitialState('subjects', $this->configService->getSubjects());
        $this->initialState->provideInitialState('classes', $this->configService->getClasses());
        $this->initialState->provideInitialState('levels', CourseLevel::ALL);

        return new TemplateResponse('kursumstufung', 'main');
    }
}
