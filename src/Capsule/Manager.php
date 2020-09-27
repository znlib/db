<?php

namespace ZnLib\Db\Capsule;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\FileHelper;
use ZnLib\Db\Enums\DbDriverEnum;
use ZnLib\Db\Facades\DbFacade;
use ZnLib\Db\Helpers\ConfigHelper;
use ZnLib\Db\Helpers\DbHelper;
use ZnLib\Db\Libs\TableAlias;
use ZnLib\Fixture\Domain\Traits\ConfigTrait;

class Manager extends CapsuleManager
{

    use ConfigTrait;

    private $tableAlias;

    public function __construct(?Container $container = null, $mainConfigFile = null)
    {
        parent::__construct($container);
        $config = $this->loadConfig($mainConfigFile);
        $this->tableAlias = new TableAlias;
        $connections = DbFacade::getConfigFromEnv();
        foreach ($connections as $connectionName => $connectionConfig) {
            if (!isset($connectionConfig['map'])) {
                $connectionConfig['map'] = ArrayHelper::getValue($config, 'connection.map', []);
            }
            $connectionConfig['database'] = $connectionConfig['dbname'];
            if ($connectionConfig['driver'] == DbDriverEnum::SQLITE) {
                FileHelper::touch($connectionConfig['database']);
            }
            $this->addConnection($connectionConfig);
            $map = ArrayHelper::getValue($connectionConfig, 'map', []);
            if ($connectionConfig['driver'] !== DbDriverEnum::PGSQL) {
                foreach ($map as $from => &$to) {
                    $to = str_replace('.', '_', $to);
                }
            }
            $this->getAlias()->addMap($connectionName, $map);
        }
        $this->bootEloquent();
    }

    public function getAlias(): TableAlias
    {
        return $this->tableAlias;
    }

    private static function getConnections(array $config): array
    {
        $defaultConnection = ArrayHelper::getValue($config, 'defaultConnection');
        $connections = ArrayHelper::getValue($config, 'connections', []);
        if ($connections) {
            if (empty($defaultConnection)) {
                if (!empty($connections['default'])) {
                    $defaultConnection = 'default';
                } else {
                    $defaultConnection = ArrayHelper::firstKey($connections);
                }
            }
            if ($defaultConnection != 'default') {
                $connections['default'] = $connections[$defaultConnection];
                unset($connections[$defaultConnection]);
            }
        } else {
            $connections = DbFacade::getConfigFromEnv();
            //dd($connections);
        }
        foreach ($connections as &$connection) {
            if (!empty($connection['dsn'])) {
                $connectionFromDsn = ConfigHelper::parseDsn($connection['dsn']);
                $connection = array_merge($connectionFromDsn, $connection);
            }
            if ($connection['driver'] == 'sqlite') {
                $connection['database'] = FileHelper::prepareRootPath($connection['database'] ?? null);
                unset($connection['host']);
            } else {
                //$connection['database'] = trim($connection['database'], '/');
                //$connection['host'] = $connection['host'] ?? '127.0.0.1';
            }
        }
        return $connections;
    }

}



/* Пример конфига
return [
    'defaultConnection' => 'mysqlServer',
    //'defaultConnection' => 'sqliteServer',
    //'defaultConnection' => 'pgsqlServer',
    'connections' => [
        'mysqlServer' => [
            "driver" => 'mysql',
            "host" => 'localhost',
            "database" => 'symfony4',
            "username" => 'root',
            "password" => '',
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ],
        'sqliteServer' => [
            "driver" => 'sqlite',
            "database" => __DIR__ . '/../../var/sqlite/default.sqlite',
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ],
        'pgsqlServer' => [
            "driver" => DbDriverEnum::PGSQL,
            "host" => 'localhost',
            "database" => 'symfony4',
            "username" => 'postgres',
            "password" => 'postgres',
            "charset" => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix" => "",
        ],
    ],
];
 */
