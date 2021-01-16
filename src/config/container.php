<?php

use Psr\Container\ContainerInterface;
use ZnCore\Domain\Interfaces\Libs\EntityManagerInterface;
use ZnCore\Domain\Libs\EntityManager;
use ZnLib\Db\Capsule\Manager;
use ZnLib\Db\Factories\ManagerFactory;

return [
    'definitions' => [],
    'singletons' => [
        EntityManagerInterface::class => EntityManager::class,
        EntityManager::class => function (ContainerInterface $container) {
            return EntityManager::getInstance($container);
        },
        Manager::class => function () {
            return ManagerFactory::createManagerFromEnv();
        },
    ],
];
