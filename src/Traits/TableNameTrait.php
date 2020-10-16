<?php

namespace ZnLib\Db\Traits;

use ZnCore\Base\Libs\DotEnv\DotEnv;

trait TableNameTrait
{

    protected $tableName;

    public function connectionName()
    {
        return $this->capsule->getConnectionNameByTableName($this->tableName());
    }

    public function tableName(): string
    {
        return $this->tableName;
    }

    public function tableNameAlias(): string
    {
        return $this->encodeTableName($this->tableName());
    }

    public function encodeTableName(string $sourceTableName): string
    {
        $tableAlias = $this->getCapsule()->getAlias();
        $targetTableName = $tableAlias->encode($this->connectionName(), $sourceTableName);
        return $targetTableName;
    }

}