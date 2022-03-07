<?php

namespace ZnLib\Db\Helpers;

use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Libs\DotEnv\DotEnv;
use ZnDatabase\Base\Domain\Enums\DbDriverEnum;

class DbHelper
{

    public static function encodeDirection($direction)
    {
        $directions = [
            SORT_ASC => 'asc',
            SORT_DESC => 'desc',
        ];
        return $directions[$direction];
    }

}