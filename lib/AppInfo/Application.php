<?php
namespace OCA\KursUmstufung\AppInfo;

use OCA\KursUmstufung\Controller\RequestController;
use OCA\KursUmstufung\Controller\PageController;
use OCA\KursUmstufung\Service\RequestService;
use OCA\KursUmstufung\Db\RequestMapper;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

class Application extends App implements IBootstrap {
    public function __construct(array $urlParams = []) {
        parent::__construct('kursumstufung', $urlParams);
    }

    public function register(IRegistrationContext $context): void {
        // Services registrieren
        $context->registerService('RequestService', function($c) {
            return new RequestService(
                $c->get('RequestMapper'),
                $c->get(\OCP\IUserManager::class)
            );
        });

        $context->registerService('RequestMapper', function($c) {
            return new RequestMapper(
                $c->get(\OCP\IDBConnection::class)
            );
        });

        // Controller registrieren
        $context->registerService('RequestController', function($c) {
            return new RequestController(
                'kursumstufung',
                $c->get(\OCP\IRequest::class),
                $c->get('RequestService')
            );
        });

        $context->registerService('PageController', function($c) {
            return new PageController(
                'kursumstufung',
                $c->get(\OCP\IRequest::class)
            );
        });
    }

    public function boot(IBootContext $context): void {
    }
}
