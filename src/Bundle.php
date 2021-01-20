<?php

namespace ZnLib\Db;

use ZnCore\Base\Libs\App\Base\BaseBundle;

class Bundle extends BaseBundle
{

    public function console(): array
    {
        return [
            'ZnLib\Db\Commands',
        ];
    }

    public function container(): array
    {
        return [
            __DIR__ . '/config/container.php',
        ];
    }
}
