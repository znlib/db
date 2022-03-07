<?php

namespace ZnLib\Db\Libs\ConfigBuilders;

use ZnDatabase\Base\Domain\Enums\DbDriverEnum;
use ZnLib\Db\Helpers\ConfigHelper;

class YiiConfigBuilder
{

    public static function build(array $connection) {
        $connection['dbname'] = $connection['database'];
        unset($connection['database']);
        $connection['host'] = $connection['host'] ?? '127.0.0.1';
        $connection = self::buildConfigForPdo($connection);
        return $connection;
    }

    private static function buildConfigForPdo(array $config): array
    {
        if ($config['driver'] == DbDriverEnum::SQLITE) {
//            dd($config);
            return [
                'dsn' => 'sqlite:' . $config['dbname'],
            ];
        } else {
            $dsnArray[] = "{$config['driver']}:host={$config['host']}";
            foreach ($config as $configName => $configValue) {
                $isExtraParam = in_array($configName, ['driver', 'host', 'username', 'password']);
                if (!empty($configValue) && !$isExtraParam && !is_array($configValue)) {
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
}
