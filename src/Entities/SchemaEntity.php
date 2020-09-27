<?php

namespace ZnLib\Db\Entities;

class SchemaEntity
{

    private $name;
    private $dbName;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function setDbName($dbName): void
    {
        $this->dbName = $dbName;
    }
    
}