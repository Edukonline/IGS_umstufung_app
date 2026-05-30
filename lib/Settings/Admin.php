<?php
namespace OCA\KursUmstufung\Settings;

use OCP\Settings\ISettings;
use OCP\IConfig;
use OCP\Template;

class Admin implements ISettings {
    private IConfig $config;

    public function __construct(IConfig $config) {
        $this->config = $config;
    }

    /**
     * Gibt das Template für die Einstellungsseite zurück
     */
    public function getForm() {
        $adminGroup = $this->config->getAppValue('kursumstufung', 'admin_group', 'schulleitung');
        return new \OCP\AppFramework\Http\TemplateResponse('kursumstufung', 'settings/admin', ['adminGroup' => $adminGroup], '');
    }

    /**
     * Sektion unter der die Einstellung auftaucht ('additional' = 'Zusätzliche Einstellungen')
     */
    public function getSection() {
        return 'additional';
    }

    /**
     * Priorität für die Sortierung (0-100)
     */
    public function getPriority() {
        return 50;
    }
}
