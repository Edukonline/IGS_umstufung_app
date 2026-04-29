<?php
namespace OCA\UmstufungMNS\AppInfo;

use OCA\UmstufungMNS\Controller\RequestController;
use OCA\UmstufungMNS\Controller\PageController;
use OCA\UmstufungMNS\Service\RequestService;
use OCA\UmstufungMNS\Db\RequestMapper;
use OCP\AppFramework\App;
use OCP\IContainer;

class Application extends App {
    public function __construct(array $urlParams = []) {
        parent::__construct('umstufungmns', $urlParams);

        $container = $this->getContainer();

        $container->registerService(RequestService::class, function (IContainer $c) {
            return new RequestService(
                $c->query(RequestMapper::class)
            );
        });

        $container->registerService(RequestMapper::class, function (IContainer $c) {
            return new RequestMapper(
                $c->query('ServerContainer')->getDatabaseConnection()
            );
        });

        $container->registerService('RequestController', function (IContainer $c) {
            return new RequestController(
                'umstufungmns',
                $c->query('Request'),
                $c->query(RequestService::class)
            );
        });

        $container->registerService('PageController', function (IContainer $c) {
            return new PageController(
                'umstufungmns',
                $c->query('Request')
            );
        });
    }
}
