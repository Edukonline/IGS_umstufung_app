<?php
namespace OCA\KursUmstufung\Controller;

use OCP\AppFramework\Controller;
use OCP\IRequest;
use OCP\IConfig;

class SettingsController extends Controller {
    private IConfig $config;

    public function __construct($AppName, IRequest $request, IConfig $config) {
        parent::__construct($AppName, $request);
        $this->config = $config;
    }

    /**
     * @NoCSRFRequired
     * @AdminRequired
     */
    public function setAdminGroup(string $groupName) {
        // Speichert die ausgewählte Gruppe global für die App
        $this->config->setAppValue('kursumstufung', 'admin_group', trim($groupName));
        return ['status' => 'success', 'group' => $groupName];
    }
}
