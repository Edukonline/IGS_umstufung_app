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
     * Speichert die Schulleitungs-Gruppe. CSRF-Schutz ist bewusst aktiv
     * (kein @NoCSRFRequired) — das Admin-Frontend sendet den requesttoken mit.
     * @AdminRequired
     */
    public function setAdminGroup(string $groupName): DataResponse {
        $groupName = trim($groupName);
        if ($groupName === '' || !$this->groupManager->groupExists($groupName)) {
            return new DataResponse(
                ['status' => 'error', 'error' => 'Die angegebene Gruppe existiert nicht.'],
                Http::STATUS_BAD_REQUEST
            );
        }
        $this->configService->setAdminGroup($groupName);
        return new DataResponse(['status' => 'success', 'group' => $groupName]);
    }

    /**
     * Speichert die wählbaren Fächer.
     * @AdminRequired
     */
    public function setSubjects(array $subjects): DataResponse {
        $this->configService->setSubjects($subjects);
        return new DataResponse(['status' => 'success', 'subjects' => $this->configService->getSubjects()]);
    }

    /**
     * Speichert die wählbaren Klassen.
     * @AdminRequired
     */
    public function setClasses(array $classes): DataResponse {
        $this->configService->setClasses($classes);
        return new DataResponse(['status' => 'success', 'classes' => $this->configService->getClasses()]);
    }
}
