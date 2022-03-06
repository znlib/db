<?php

namespace ZnLib\Db\Base;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Collection;
use ZnCore\Domain\Interfaces\GetEntityClassInterface;
use ZnCore\Domain\Interfaces\Libs\EntityManagerInterface;
use ZnCore\Domain\Traits\EntityManagerTrait;
use ZnLib\Db\Capsule\Manager;
use ZnLib\Db\Traits\EloquentTrait;
use ZnLib\Db\Traits\MapperTrait;
use ZnLib\Db\Traits\TableNameTrait;

abstract class BaseEloquentRepository implements GetEntityClassInterface
{

    use EloquentTrait;
    use TableNameTrait;
    use EntityManagerTrait;
    use MapperTrait;

    protected $autoIncrement = 'id';
    private $entityClassName;

    public function __construct(EntityManagerInterface $em, Manager $capsule)
    {
        $this->setCapsule($capsule);
        $this->setEntityManager($em);
    }

    public function autoIncrement()
    {
        return $this->autoIncrement;
    }

    /*public function getConnection(): Connection
    {
        $connection = $this->capsule->getConnection($this->connectionName());
        return $connection;
    }*/

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->getQueryBuilderByTableName($this->tableName());
//        $connection = $this->getConnection();
//        $queryBuilder = $connection->table($this->tableNameAlias(), null);
//        return $queryBuilder;
    }

    /*protected function getSchema(): SchemaBuilder
    {
        $connection = $this->getConnection();
        $schema = $connection->getSchemaBuilder();
        return $schema;
    }*/

    /*function getAttributeMap(): array {
        return [

        ];
    }*/

    protected function allByBuilder(QueryBuilder $queryBuilder): Collection
    {
        $postCollection = $queryBuilder->get();
        $array = $postCollection->toArray();
        $collection = $this->mapperDecodeCollection($array);
        return $collection;
    }

    public function setEntityClass(object $entityClass): void
    {
        $this->entityClassName = $entityClass;
    }

    public function getEntityClass(): string
    {
        return $this->entityClassName;
    }

    /*protected function oneByBuilder(QueryBuilder $queryBuilder)
    {
        $item = $queryBuilder->first();
        if (empty($item)) {
            throw new NotFoundException('Not found entity!');
        }
        return $this->forgeEntity($item);
    }*/

}
