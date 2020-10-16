<?php

use Symfony\Component\Console\Application;
use ZnLib\Db\Capsule\Manager;
use Illuminate\Container\Container;
use ZnLib\Console\Symfony4\Helpers\CommandHelper;

/**
 * @var Application $application
 * @var Container $container
 */

$capsule = $container->get(Manager::class);

CommandHelper::registerFromNamespaceList([
    'ZnLib\Db\Commands',
], $container);
