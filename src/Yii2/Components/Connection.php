<?php

namespace ZnLib\Db\Yii2\Components;

use ZnLib\Db\Facades\DbFacade;
use ZnLib\Db\Helpers\ConfigHelper;

class Connection extends \yii\db\Connection
{

    public $charset = 'utf8';
    public $enableSchemaCache = YII_ENV_PROD;

    public function __construct(array $config = [])
    {
        if (empty($config)) {
            $connections = DbFacade::getConfigFromEnv();
            $config = $connections['default'];
            $config = ConfigHelper::buildConfigForPdo($config);
        }
        parent::__construct($config);
    }

}