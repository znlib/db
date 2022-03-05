<?php

namespace ZnLib\Db\Traits;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use ZnLib\Db\Capsule\Manager;

trait EloquentTrait
{

    protected $capsule;

    abstract public function connectionName();

    public function getCapsule(): Manager
    {
        return $this->capsule;
    }

    public function getConnection(): Connection
    {
        $connection = $this->getCapsule()->getConnection($this->connectionName());
        return $connection;
    }

    protected function getSchema(): SchemaBuilder
    {
        $connection = $this->getConnection();
        $schema = $connection->getSchemaBuilder();
        return $schema;
    }

}
