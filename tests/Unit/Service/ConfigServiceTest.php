<?php
namespace OCA\KursUmstufung\Tests\Unit\Service;

use OCA\KursUmstufung\Service\ConfigService;
use OCP\IConfig;
use PHPUnit\Framework\TestCase;

class ConfigServiceTest extends TestCase {
    /** @var IConfig&\PHPUnit\Framework\MockObject\MockObject */
    private $config;
    private ConfigService $service;

    protected function setUp(): void {
        parent::setUp();
        $this->config = $this->createMock(IConfig::class);
        $this->service = new ConfigService($this->config);
    }

    public function testGetSubjectsFallsBackToDefaults(): void {
        $this->config->method('getAppValue')->willReturn('');
        $this->assertContains('Mathematik', $this->service->getSubjects());
    }

    public function testGetSubjectsParsesStoredJson(): void {
        $this->config->method('getAppValue')->willReturn(json_encode(['Kunst', 'Musik']));
        $this->assertSame(['Kunst', 'Musik'], $this->service->getSubjects());
    }

    public function testGetSubjectsIgnoresCorruptJson(): void {
        $this->config->method('getAppValue')->willReturn('{not-json');
        $this->assertContains('Deutsch', $this->service->getSubjects());
    }

    public function testGetClassesGeneratesDefaultGrid(): void {
        $this->config->method('getAppValue')->willReturn('');
        $classes = $this->service->getClasses();
        $this->assertContains('5a', $classes);
        $this->assertContains('10c', $classes);
    }

    public function testSetSubjectsTrimsAndDropsEmpty(): void {
        $this->config->expects($this->once())
            ->method('setAppValue')
            ->with('kursumstufung', 'subjects', json_encode(['Mathe', 'Physik']));
        $this->service->setSubjects([' Mathe ', '', 'Physik', '   ']);
    }
}
