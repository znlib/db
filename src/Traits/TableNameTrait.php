<?php

namespace ZnLib\Db\Traits;

trait TableNameTrait
{

    protected $connectionName = 'default';
    protected $tableName;

    //abstract function getCapsule() : Manager;

    public function connectionName(): string
    {
        return $this->connectionName;
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