<?php

use Symfony\Component\Console\Application;
use ZnLib\Db\Capsule\Manager;

/**
 * @var Application $application
 */

$eloquentConfigFile = $_ENV['ELOQUENT_CONFIG_FILE'];
$capsule = new Manager(null, $eloquentConfigFile);

use ZnLib\Db\Commands\DeleteAllTablesCommand;

// создаем и объявляем команду "deleteAllTables"
$deleteAllTablesCommand = new DeleteAllTablesCommand(DeleteAllTablesCommand::getDefaultName(), $fixtureService);
$application->add($deleteAllTablesCommand);
