<?php

namespace ZnLib\Db\Capsule;

use Illuminate\Database\Capsule\Manager as CapsuleManager;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnLib\Db\Libs\TableAlias;

class Manager extends CapsuleManager
{

    private $tableAlias;
    private $connectionMap = [];

    public function getTableAlias(): TableAlias
    {
        return $this->tableAlias;
    }

    public function setTableAlias(TableAlias $tableAlias): void
    {
        $this->tableAlias = $tableAlias;
    }

    /**
     * @return TableAlias
     * @deprecated
     * @see getTableAlias
     */
    public function getAlias(): TableAlias
    {
        return $this->tableAlias;
    }

    public function getConnectionNameByTableName(string $tableName)
    {
        return ArrayHelper::getValue($this->connectionMap, $tableName, 'default');
    }

    public function getConnectionMap(): array
    {
        return $this->connectionMap;
    }

    public function setConnectionMap($connectionMap): void
    {
        $this->connectionMap = $connectionMap;
    }
}
