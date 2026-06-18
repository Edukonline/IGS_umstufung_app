<?php
namespace OCA\KursUmstufung\Tests\Unit\Service;

use OCA\KursUmstufung\Constants\RequestStatus;
use OCA\KursUmstufung\Db\Request;
use OCA\KursUmstufung\Db\RequestMapper;
use OCA\KursUmstufung\Exception\ForbiddenException;
use OCA\KursUmstufung\Exception\ValidationException;
use OCA\KursUmstufung\Service\ConfigService;
use OCA\KursUmstufung\Service\NotificationService;
use OCA\KursUmstufung\Service\RequestService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUserManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RequestServiceTest extends TestCase {
    /** @var RequestMapper&MockObject */
    private $mapper;
    /** @var IUserManager&MockObject */
    private $userManager;
    /** @var ConfigService&MockObject */
    private $configService;
    /** @var NotificationService&MockObject */
    private $notificationService;

    private RequestService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->mapper = $this->createMock(RequestMapper::class);
        $this->userManager = $this->createMock(IUserManager::class);
        $this->configService = $this->createMock(ConfigService::class);
        $this->notificationService = $this->createMock(NotificationService::class);

        $this->configService->method('getSubjects')->willReturn(['Mathematik', 'Deutsch']);
        $this->configService->method('getClasses')->willReturn(['5a', '6b']);
        $this->userManager->method('get')->willReturn(null);

        $this->service = new RequestService(
            $this->mapper,
            $this->userManager,
            $this->configService,
            $this->notificationService
        );
    }

    private function validData(array $overrides = []): array {
        return array_merge([
            'studentName' => 'Max Mustermann',
            'class' => '5a',
            'subject' => 'Mathematik',
            'oldLevel' => 'G-Kurs',
            'newLevel' => 'E-Kurs',
            'reason' => 'Sehr gute Leistungen.',
        ], $overrides);
    }

    private function submittedRequest(string $owner = 'teacher'): Request {
        $request = new Request();
        $request->setUserId($owner);
        $request->setStudentName('Max Mustermann');
        $request->setStatus(RequestStatus::SUBMITTED);
        return $request;
    }

    public function testCreateProducesDraftWithSchoolYear(): void {
        $this->mapper->method('insert')->willReturnArgument(0);

        $result = $this->service->create('teacher', $this->validData());

        $this->assertSame(RequestStatus::DRAFT, $result->getStatus());
        $this->assertSame('teacher', $result->getUserId());
        $this->assertMatchesRegularExpression('/^\d{4}\/\d{4}$/', $result->getSchoolYear());
    }

    public function testCreateRejectsEmptyName(): void {
        $this->expectException(ValidationException::class);
        $this->service->create('teacher', $this->validData(['studentName' => '   ']));
    }

    public function testCreateRejectsUnknownSubject(): void {
        $this->expectException(ValidationException::class);
        $this->service->create('teacher', $this->validData(['subject' => 'Sport']));
    }

    public function testCreateRejectsUnknownClass(): void {
        $this->expectException(ValidationException::class);
        $this->service->create('teacher', $this->validData(['class' => '9z']));
    }

    public function testCreateRejectsInvalidLevel(): void {
        $this->expectException(ValidationException::class);
        $this->service->create('teacher', $this->validData(['oldLevel' => 'Z-Kurs']));
    }

    public function testCreateRejectsIdenticalLevels(): void {
        $this->expectException(ValidationException::class);
        $this->service->create('teacher', $this->validData(['oldLevel' => 'E-Kurs', 'newLevel' => 'E-Kurs']));
    }

    public function testUpdateForbiddenForForeignRequest(): void {
        $request = new Request();
        $request->setUserId('other');
        $request->setStatus(RequestStatus::DRAFT);
        $this->mapper->method('findById')->willReturn($request);

        $this->expectException(ForbiddenException::class);
        $this->service->update(1, 'teacher', $this->validData());
    }

    public function testUpdateForbiddenWhenNotDraft(): void {
        $request = new Request();
        $request->setUserId('teacher');
        $request->setStatus(RequestStatus::SUBMITTED);
        $this->mapper->method('findById')->willReturn($request);

        $this->expectException(ForbiddenException::class);
        $this->service->update(1, 'teacher', $this->validData());
    }

    public function testDeleteForbiddenForForeignRequest(): void {
        $request = new Request();
        $request->setUserId('other');
        $request->setStatus(RequestStatus::DRAFT);
        $this->mapper->method('findById')->willReturn($request);

        $this->expectException(ForbiddenException::class);
        $this->service->delete(1, 'teacher');
    }

    public function testDeleteSoftDeletesInsteadOfRemoving(): void {
        $request = new Request();
        $request->setUserId('teacher');
        $request->setStatus(RequestStatus::DRAFT);
        $this->mapper->method('findById')->willReturn($request);
        $this->mapper->expects($this->never())->method('delete');
        $this->mapper->expects($this->once())
            ->method('update')
            ->with($this->callback(static fn (Request $r) => $r->getDeletedAt() instanceof \DateTimeInterface));

        $this->service->delete(1, 'teacher');
    }

    public function testRestoreRevivesDeletedOwnedRequest(): void {
        $request = new Request();
        $request->setUserId('teacher');
        $request->setStatus(RequestStatus::DRAFT);
        $request->setDeletedAt(new \DateTime());
        $this->mapper->method('findById')->willReturn($request);
        $this->mapper->method('update')->willReturnArgument(0);

        $result = $this->service->restore(1, 'teacher');

        $this->assertNull($result->getDeletedAt());
    }

    public function testRestoreForbiddenForForeignRequest(): void {
        $request = new Request();
        $request->setUserId('other');
        $request->setStatus(RequestStatus::DRAFT);
        $request->setDeletedAt(new \DateTime());
        $this->mapper->method('findById')->willReturn($request);

        $this->expectException(ForbiddenException::class);
        $this->service->restore(1, 'teacher');
    }

    public function testRestoreForbiddenWhenNotDeleted(): void {
        $request = new Request();
        $request->setUserId('teacher');
        $request->setStatus(RequestStatus::DRAFT);
        $this->mapper->method('findById')->willReturn($request);

        $this->expectException(ForbiddenException::class);
        $this->service->restore(1, 'teacher');
    }

    public function testDecideRejectsInvalidDecision(): void {
        $this->expectException(ValidationException::class);
        $this->service->decide(1, 'head', RequestStatus::DRAFT);
    }

    public function testDecideForbiddenWhenNotSubmitted(): void {
        $request = new Request();
        $request->setUserId('teacher');
        $request->setStatus(RequestStatus::APPROVED);
        $this->mapper->method('findById')->willReturn($request);

        $this->expectException(ForbiddenException::class);
        $this->service->decide(1, 'head', RequestStatus::APPROVED);
    }

    public function testDecideApprovesAndNotifies(): void {
        $request = $this->submittedRequest();
        $this->mapper->method('findById')->willReturn($request);
        $this->mapper->method('update')->willReturnArgument(0);
        $this->notificationService->expects($this->once())
            ->method('notifyDecided')
            ->with($this->isInstanceOf(Request::class), RequestStatus::APPROVED);

        $result = $this->service->decide(1, 'head', RequestStatus::APPROVED, 'Passt.');

        $this->assertSame(RequestStatus::APPROVED, $result->getStatus());
        $this->assertSame('head', $result->getDecidedBy());
        $this->assertSame('Passt.', $result->getDecisionReason());
    }

    public function testSubmitAllNotifiesWhenDraftsExist(): void {
        $this->mapper->method('submitAllDraftsForUser')->willReturn(3);
        $this->notificationService->expects($this->once())
            ->method('notifySubmitted')
            ->with('teacher', 3);

        $this->assertSame(3, $this->service->submitAllDraftsForUser('teacher'));
    }

    public function testSubmitAllSkipsNotificationWhenNothingSubmitted(): void {
        $this->mapper->method('submitAllDraftsForUser')->willReturn(0);
        $this->notificationService->expects($this->never())->method('notifySubmitted');

        $this->assertSame(0, $this->service->submitAllDraftsForUser('teacher'));
    }

    public function testUpdatePropagatesNotFound(): void {
        $this->mapper->method('findById')->willThrowException(new DoesNotExistException('x'));

        $this->expectException(DoesNotExistException::class);
        $this->service->update(99, 'teacher', $this->validData());
    }
}
