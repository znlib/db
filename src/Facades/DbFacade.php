<?php

namespace ZnLib\Db\Facades;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Libs\DotEnv\DotEnv;
use ZnLib\Db\Helpers\ConfigHelper;

class DbFacade
{

    public static function getConfigFromEnv(): array
    {
        if (!empty($_ENV['DATABASE_URL'])) {
            $connections['default'] = ConfigHelper::parseDsn($_ENV['DATABASE_URL']);
        } else {
            $config = DotEnv::get('db');
            $isFlatConfig = !is_array(ArrayHelper::first($config));
            if ($isFlatConfig) {
                $connections['default'] = $config;
            } else {
                $connections = $config;
            }
        }
        foreach ($connections as &$connection) {
            $connection = ConfigHelper::prepareConfig($connection);
        }
        return $connections;
    }

}