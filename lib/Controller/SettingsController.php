<?php
namespace OCA\KursUmstufung\Controller;

use OCA\KursUmstufung\Service\ConfigService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\IGroupManager;
use OCP\IRequest;

class SettingsController extends Controller {
    private ConfigService $configService;
    private IGroupManager $groupManager;

    public function __construct(
        string $appName,
        IRequest $request,
        ConfigService $configService,
        IGroupManager $groupManager
    ) {
        parent::__construct($appName, $request);
        $this->configService = $configService;
        $this->groupManager = $groupManager;
    }

    /**
     * Speichert alle Admin-Einstellungen in einem Schritt. Validiert zuerst die
     * Gruppe und schreibt nur, wenn sie existiert — kein inkonsistenter
     * Teilzustand mehr. Admin + CSRF sind der korrekte Default (kein
     * @NoAdminRequired/@NoCSRFRequired).
     *
     * @AdminRequired
     */
    public function save(string $groupName, array $subjects, array $classes): DataResponse {
        $groupName = trim($groupName);
        if ($groupName === '' || !$this->groupManager->groupExists($groupName)) {
            return new DataResponse(
                ['status' => 'error', 'error' => 'Die angegebene Gruppe existiert nicht.'],
                Http::STATUS_BAD_REQUEST
            );
        }

        $this->configService->setAdminGroup($groupName);
        $this->configService->setSubjects($subjects);
        $this->configService->setClasses($classes);

        return new DataResponse([
            'status' => 'success',
            'group' => $groupName,
            'subjects' => $this->configService->getSubjects(),
            'classes' => $this->configService->getClasses(),
        ]);
    }
}
