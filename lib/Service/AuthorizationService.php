<?php
namespace OCA\KursUmstufung\Service;

use OCP\IGroupManager;
use OCP\IUserSession;

/**
 * Bündelt die Rollenlogik (Lehrkraft vs. Schulleitung) an einer Stelle.
 * Ersetzt die zuvor im Controller via \OC::$server eingestreuten,
 * nicht mockbaren Service-Locator-Aufrufe durch echte Dependency Injection.
 */
class AuthorizationService {
    private IUserSession $userSession;
    private IGroupManager $groupManager;
    private ConfigService $configService;

    public function __construct(
        IUserSession $userSession,
        IGroupManager $groupManager,
        ConfigService $configService
    ) {
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
        $this->configService = $configService;
    }

    public function getUserId(): string {
        $user = $this->userSession->getUser();
        return $user !== null ? $user->getUID() : '';
    }

    public function isSchulleitung(?string $userId = null): bool {
        $userId = $userId ?? $this->getUserId();
        if ($userId === '') {
            return false;
        }
        $adminGroup = $this->configService->getAdminGroup();
        return $this->groupManager->isAdmin($userId)
            || $this->groupManager->isInGroup($userId, $adminGroup);
    }
}
