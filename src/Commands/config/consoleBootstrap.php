<?php

use Psr\Container\ContainerInterface;
use ZnCore\Base\Libs\App\Helpers\ContainerHelper;
use ZnCore\Base\Libs\App\Kernel;
use ZnCore\Base\Libs\App\Loaders\BundleLoader;
use ZnCore\Base\Libs\DotEnv\DotEnv;
use ZnCore\Domain\Interfaces\Libs\EntityManagerInterface;
use ZnCore\Domain\Libs\EntityManager;
use ZnLib\Db\Capsule\Manager;
use ZnDatabase\Eloquent\Domain\Factories\ManagerFactory;

DotEnv::init();

$kernel = new Kernel('console');
$container = $kernel->getContainer();

$containerConfigurator = ContainerHelper::getContainerConfiguratorByContainer($container);
$containerConfigurator->singleton(EntityManagerInterface::class, function (ContainerInterface $container) {
    return EntityManager::getInstance($container);
});
$containerConfigurator->singleton(Manager::class, function () {
    return ManagerFactory::createManagerFromEnv();
});

/*$container->singleton(EntityManagerInterface::class, function (ContainerInterface $container) {
    return EntityManager::getInstance($container);
});
$container->singleton(Manager::class, function () {
    return ManagerFactory::createManagerFromEnv();
});*/

$bundleLoader = new BundleLoader([], ['i18next', 'container', 'console', 'migration']);
$bundleLoader->addBundles(include __DIR__ . '/bundle.php');
$kernel->setLoader($bundleLoader);

$config = $kernel->loadAppConfig();
