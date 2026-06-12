<?php
namespace OCA\KursUmstufung\Tests\Unit\Service;

use OCA\KursUmstufung\Service\AuthorizationService;
use OCA\KursUmstufung\Service\ConfigService;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class AuthorizationServiceTest extends TestCase {
    /** @var IUserSession&\PHPUnit\Framework\MockObject\MockObject */
    private $userSession;
    /** @var IGroupManager&\PHPUnit\Framework\MockObject\MockObject */
    private $groupManager;
    /** @var ConfigService&\PHPUnit\Framework\MockObject\MockObject */
    private $configService;

    private AuthorizationService $auth;

    protected function setUp(): void {
        parent::setUp();
        $this->userSession = $this->createMock(IUserSession::class);
        $this->groupManager = $this->createMock(IGroupManager::class);
        $this->configService = $this->createMock(ConfigService::class);
        $this->configService->method('getAdminGroup')->willReturn('schulleitung');

        $this->auth = new AuthorizationService(
            $this->userSession,
            $this->groupManager,
            $this->configService
        );
    }

    public function testGetUserIdReturnsEmptyWhenAnonymous(): void {
        $this->userSession->method('getUser')->willReturn(null);
        $this->assertSame('', $this->auth->getUserId());
    }

    public function testIsSchulleitungFalseForAnonymous(): void {
        $this->userSession->method('getUser')->willReturn(null);
        $this->assertFalse($this->auth->isSchulleitung());
    }

    public function testIsSchulleitungTrueForNextcloudAdmin(): void {
        $this->groupManager->method('isAdmin')->with('alice')->willReturn(true);
        $this->groupManager->method('isInGroup')->willReturn(false);
        $this->assertTrue($this->auth->isSchulleitung('alice'));
    }

    public function testIsSchulleitungTrueWhenInConfiguredGroup(): void {
        $this->groupManager->method('isAdmin')->willReturn(false);
        $this->groupManager->method('isInGroup')->with('bob', 'schulleitung')->willReturn(true);
        $this->assertTrue($this->auth->isSchulleitung('bob'));
    }

    public function testIsSchulleitungFalseForRegularTeacher(): void {
        $this->groupManager->method('isAdmin')->willReturn(false);
        $this->groupManager->method('isInGroup')->willReturn(false);
        $this->assertFalse($this->auth->isSchulleitung('carol'));
    }

    public function testGetUserIdReturnsUid(): void {
        $user = $this->createMock(IUser::class);
        $user->method('getUID')->willReturn('dave');
        $this->userSession->method('getUser')->willReturn($user);
        $this->assertSame('dave', $this->auth->getUserId());
    }
}
