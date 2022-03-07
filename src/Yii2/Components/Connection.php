<?php

namespace ZnLib\Db\Yii2\Components;

use ZnDatabase\Base\Domain\Facades\DbFacade;
use ZnDatabase\Base\Domain\Helpers\ConfigHelper;
use ZnLib\Db\Libs\ConfigBuilders\YiiConfigBuilder;

// todo: переметсить в наработки Yii

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