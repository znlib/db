<?php

use Symfony\Component\Console\Application;
use ZnLib\Db\Capsule\Manager;

/**
 * @var Application $application
 */

$capsule = \ZnLib\Db\Factories\ManagerFactory::createManagerFromEnv();

use ZnLib\Db\Commands\DeleteAllTablesCommand;

// создаем и объявляем команду "deleteAllTables"
$deleteAllTablesCommand = new DeleteAllTablesCommand(DeleteAllTablesCommand::getDefaultName(), $fixtureService);
$application->add($deleteAllTablesCommand);
