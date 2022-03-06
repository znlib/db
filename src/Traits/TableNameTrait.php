<?php

namespace ZnLib\Db\Traits;

use ZnCore\Base\Libs\DotEnv\DotEnv;
use ZnLib\Db\Capsule\Manager;
use ZnLib\Db\Libs\TableAlias;

trait TableNameTrait
{

    protected $tableName;

    abstract public function getCapsule(): Manager;

    public function connectionName()
    {
        return $this
            ->getCapsule()
            ->getConnectionNameByTableName($this->tableName());
    }

    public function tableName(): string
    {
        return $this->tableName;
    }

    public function tableNameAlias(): string
    {
        return $this->encodeTableName($this->tableName());
    }

    protected function getAlias(): TableAlias
    {
        return $this
            ->getCapsule()
            ->getAlias();
    }
    
    public function encodeTableName(string $sourceTableName, string $connectionName = null): string
    {
        $connectionName = $connectionName ?: $this->connectionName();
        $targetTableName = $this
            ->getCapsule()
            ->getAlias()
            ->encode($connectionName, $sourceTableName);
        return $targetTableName;
    }
}