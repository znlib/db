<?php

use Symfony\Component\Console\Application;
use ZnLib\Db\Capsule\Manager;
use Illuminate\Container\Container;
use ZnLib\Console\Symfony4\Helpers\CommandHelper;
use Psr\Container\ContainerInterface;
use ZnCore\Domain\Libs\EntityManager;
use ZnCore\Domain\Interfaces\Libs\EntityManagerInterface;

/**
 * @var Application $application
 * @var Container $container
 */

$capsule = $container->get(Manager::class);

$em = new EntityManager($container);
$container->bind(EntityManagerInterface::class, function (ContainerInterface $container) use ($em) {
    return $em;
});

CommandHelper::registerFromNamespaceList([
    'ZnLib\Db\Commands',
], $container);
