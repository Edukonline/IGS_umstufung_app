<?php
namespace OCA\KursUmstufung\Settings;

use OCA\KursUmstufung\Service\ConfigService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Settings\ISettings;

class Admin implements ISettings {
    private ConfigService $configService;

    public function __construct(ConfigService $configService) {
        $this->configService = $configService;
    }

    public function getForm(): TemplateResponse {
        return new TemplateResponse('kursumstufung', 'settings/admin', [
            'adminGroup' => $this->configService->getAdminGroup(),
            'subjects' => implode("\n", $this->configService->getSubjects()),
            'classes' => implode("\n", $this->configService->getClasses()),
        ], '');
    }

    public function getSection(): string {
        return 'additional';
    }

    public function getPriority(): int {
        return 50;
    }
}
