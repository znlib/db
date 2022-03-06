<?php

namespace ZnLib\Db\Entities;

class RelationEntity
{

    protected $constraintName;
    protected $tableName;
    protected $columnName;
    protected $foreignTableName;
    protected $foreignColumnName;

    public function getConstraintName()
    {
        return $this->constraintName;
    }

    public function setConstraintName($constraintName): void
    {
        $this->constraintName = $constraintName;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName): void
    {
        $this->tableName = $tableName;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function setColumnName($columnName): void
    {
        $this->columnName = $columnName;
    }

    public function getForeignTableName()
    {
        return $this->foreignTableName;
    }

    public function setForeignTableName($foreignTableName): void
    {
        $this->foreignTableName = $foreignTableName;
    }

    public function getForeignColumnName()
    {
        return $this->foreignColumnName;
    }

    public function setForeignColumnName($foreignColumnName): void
    {
        $this->foreignColumnName = $foreignColumnName;
    }
}
