<?php

namespace ZnLib\Db\Helpers;

use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Libs\DotEnv\DotEnv;
use ZnLib\Db\Enums\DbDriverEnum;

class ConfigHelper
{

    public static function parseDsn($dsn)
    {
        $dsnConfig = parse_url($dsn);
        $dsnConfig = array_map('rawurldecode', $dsnConfig);
        $connectionCofig = [
            'driver' => ArrayHelper::getValue($dsnConfig, 'scheme'),
            'host' => ArrayHelper::getValue($dsnConfig, 'host', '127.0.0.1'),
            'dbname' => trim(ArrayHelper::getValue($dsnConfig, 'path'), '/'),
            'username' => ArrayHelper::getValue($dsnConfig, 'user'),
            'password' => ArrayHelper::getValue($dsnConfig, 'pass'),
        ];
        return $connectionCofig;
    }

    public static function buildConfigForPdo(array $config): array
    {
        if ($config['driver'] == DbDriverEnum::SQLITE) {
            return [
                'dsn' => 'sqlite:' . $config['dbname'],
            ];
        } else {
            $dsnArray[] = "{$config['driver']}:host={$config['host']}";
            foreach ($config as $configName => $configValue) {
                if (!empty($configValue) && !in_array($configName, ['driver', 'host', 'username', 'password'])) {
                    $dsnArray[] = "$configName=$configValue";
                }
            }
            return [
                "username" => $config['username'] ?? '',
                "password" => $config['password'] ?? '',
                "dsn" => implode(';', $dsnArray),
            ];
        }
    }

    public static function prepareConfig($connection)
    {
        $connection['driver'] = $connection['driver'] ?? $connection['connection'];
        $connection['dbname'] = $connection['dbname'] ?? $connection['database'];

        $connection['host'] = $connection['host'] ?? '127.0.0.1';
        $connection['driver'] = $connection['driver'] ?? 'mysql';

        if (!empty($connection['dbname'])) {
            $connection['dbname'] = rtrim($connection['dbname'], '/');
        }

        unset($connection['database']);
        unset($connection['connection']);

        return $connection;
    }

}