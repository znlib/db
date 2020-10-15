<?php

namespace ZnLib\Db\Factories;

use http\Params;
use ZnCore\Base\Helpers\LoadHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\FileHelper;
use ZnLib\Db\Capsule\Manager;
use ZnLib\Db\Enums\DbDriverEnum;
use ZnLib\Db\Facades\DbFacade;
use ZnLib\Db\Libs\ConfigBuilders\EloquentConfigBuilder;
use ZnLib\Db\Libs\TableAlias;

class ManagerFactory
{

    public static function createManagerFromEnv(): Manager
    {
        $connections = DbFacade::getConfigFromEnv();
        $config = LoadHelper::loadConfig($_ENV['ELOQUENT_CONFIG_FILE']);
        $connectionMap = ArrayHelper::getValue($config, 'connection.connectionMap', []);

        $map = ArrayHelper::getValue($config, 'connection.map', []);
        $tableAlias = self::createTableAlias($connections, $map);
        $capsule = new Manager;
        $capsule->setConnectionMap($connectionMap);
        $capsule->setTableAlias($tableAlias);

        self::touchSqlite($connections);

        foreach ($connections as $connectionName => $connection) {
            $capsule->addConnection(EloquentConfigBuilder::build($connection), $connectionName);
        }
        return $capsule;
    }

    private static function touchSqlite(array $connections)
    {
        foreach ($connections as $connectionName => $connectionConfig) {
            if ($connectionConfig['driver'] == DbDriverEnum::SQLITE) {
                FileHelper::touch($connectionConfig['database']);
            }
        }
    }

    private static function createTableAlias(array $connections, array $configMap): TableAlias
    {
        $tableAlias = new TableAlias;
        foreach ($connections as $connectionName => $connectionConfig) {
            if (!isset($connectionConfig['map'])) {
                $connectionConfig['map'] = $configMap;
            }
            $map = ArrayHelper::getValue($connectionConfig, 'map', []);
            if ($connectionConfig['driver'] !== DbDriverEnum::PGSQL) {
                foreach ($map as $from => &$to) {
                    $to = str_replace('.', '_', $to);
                }
            }
            $tableAlias->addMap($connectionName, $map);
        }
        return $tableAlias;
    }
}