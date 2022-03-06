<?php

namespace ZnLib\Db\Entities;

use Illuminate\Support\Collection;
use ZnLib\Db\Entities\ColumnEntity;
use ZnLib\Db\Entities\RelationEntity;

class TableEntity
{

    protected $name;
    protected $schemaName;
    protected $dbName;
    protected $columns;
    protected $relations;
    protected $schema;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getSchemaName()
    {
        return $this->schemaName;
    }

    public function setSchemaName($schemaName): void
    {
        $this->schemaName = $schemaName;
    }

    public function getDbName()
    {
        return $this->dbName;
    }

    public function setDbName($dbName): void
    {
        $this->dbName = $dbName;
    }

    /**
     * @return Collection | ColumnEntity[]
     */
    public function getColumns(): Collection
    {
        return $this->columns;
    }

    public function setColumns(Collection $columns): void
    {
        $this->columns = $columns;
    }

    /**
     * @return Collection | RelationEntity[]
     */
    public function getRelations(): ?Collection
    {
        return $this->relations;
    }

    public function setRelations(?Collection $relations): void
    {
        $this->relations = $relations;
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