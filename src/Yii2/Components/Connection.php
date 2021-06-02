<?php

namespace ZnLib\Db\Yii2\Components;

use ZnLib\Db\Facades\DbFacade;
use ZnLib\Db\Helpers\ConfigHelper;
use ZnLib\Db\Libs\ConfigBuilders\YiiConfigBuilder;

class Connection extends \yii\db\Connection
{

    public $charset = 'utf8';
    public $enableSchemaCache = YII_ENV_PROD;

    public function __construct(array $config = [], $connectionName = "default")
    {
        if (empty($config)) {
            $connections = DbFacade::getConfigFromEnv();
            $config = YiiConfigBuilder::build($connections[$connectionName]);
        }
        parent::__construct($config);
    }

}