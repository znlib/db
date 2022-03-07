<?php

use Psr\Container\ContainerInterface;
use ZnCore\Domain\Interfaces\Libs\EntityManagerInterface;
use ZnCore\Domain\Libs\EntityManager;
use ZnLib\Db\Capsule\Manager;
use ZnDatabase\Eloquent\Domain\Factories\ManagerFactory;

return [
    'definitions' => [],
    'singletons' => [
        /*EntityManagerInterface::class => EntityManager::class,
        EntityManager::class => function (ContainerInterface $container) {
            return EntityManager::getInstance($container);
        },*/
        EntityManagerInterface::class => function (ContainerInterface $container) {
            $em = EntityManager::getInstance($container);
//            $eloquentOrm = $container->get(EloquentOrm::class);
//            $em->addOrm($eloquentOrm);
            return $em;
        },
        Manager::class => function () {
            return ManagerFactory::createManagerFromEnv();
        },
    ],
];
