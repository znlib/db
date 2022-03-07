<?php

namespace ZnLib\Db\Helpers;

use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Libs\DotEnv\DotEnv;
use ZnDatabase\Base\Domain\Enums\DbDriverEnum;

class ConfigHelper
{

    public static function parseDsn($dsn)
    {
        $dsnConfig = parse_url($dsn);
        $dsnConfig = array_map('rawurldecode', $dsnConfig);
        $connectionCofig = [
            'driver' => ArrayHelper::getValue($dsnConfig, 'scheme'),
            'host' => ArrayHelper::getValue($dsnConfig, 'host', '127.0.0.1'),
            'database' => trim(ArrayHelper::getValue($dsnConfig, 'path'), '/'),
            'username' => ArrayHelper::getValue($dsnConfig, 'user'),
            'password' => ArrayHelper::getValue($dsnConfig, 'pass'),
        ];
        return $connectionCofig;
    }



    public static function prepareConfig($connection)
    {
        if (!empty($connection['database'])) {
            $connection['database'] = rtrim($connection['database'], '/');
        }

        if (!empty($connection['read']['host'])) {

            $connection['read']['host'] = explode(',', $connection['read']['host']);
        }

        if (!empty($connection['write']['host'])) {
            $connection['write']['host'] = explode(',', $connection['write']['host']);
        }

        return $connection;
    }

}