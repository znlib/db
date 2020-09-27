<?php

namespace ZnLib\Db\Entities;

class TableEntity
{

    private $name;
    private $schema;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getSchema(): SchemaEntity
    {
        return $this->schema;
    }
    
    public function setSchema(SchemaEntity $schema): void
    {
        $this->schema = $schema;
    }
    
}