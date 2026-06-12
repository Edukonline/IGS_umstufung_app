<?php
namespace OCA\KursUmstufung\AppInfo;

use OCA\KursUmstufung\Notification\Notifier;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
    public const APP_ID = 'kursumstufung';

    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // Controller und Services werden vom Nextcloud-Container automatisch
        // per Konstruktor-Typehints aufgelöst (Auto-Wiring) — keine manuellen
        // Factory-Closures mehr nötig.
        $context->registerNotifierService(Notifier::class);
    }

    public function boot(IBootContext $context): void {
    }
}
