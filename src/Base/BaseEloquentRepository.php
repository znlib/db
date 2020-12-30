<?php

namespace ZnLib\Db\Base;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Collection;
use ZnCore\Domain\Libs\EntityManager;
use ZnLib\Db\Capsule\Manager;
use ZnLib\Db\Traits\TableNameTrait;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Interfaces\GetEntityClassInterface;

abstract class BaseEloquentRepository implements GetEntityClassInterface
{

    use TableNameTrait;

    protected $autoIncrement = 'id';
    private $capsule;
    private $em;

    public function __construct(EntityManager $em, Manager $capsule)
    {
        $this->capsule = $capsule;
        $this->em = $em;
    }

    public function autoIncrement()
    {
        return $this->autoIncrement;
    }

    protected function getEntityManager(): EntityManager
    {
        return $this->em;
    }

    public function getCapsule(): Manager
    {
        return $this->capsule;
    }

    public function getConnection(): Connection
    {
        $connection = $this->capsule->getConnection($this->connectionName());
        return $connection;
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        $connection = $this->getConnection();
        $queryBuilder = $connection->table($this->tableNameAlias(), null);
        return $queryBuilder;
    }

    protected function getSchema(): SchemaBuilder
    {
        $connection = $this->getConnection();
        $schema = $connection->getSchemaBuilder();
        return $schema;
    }

    function getAttributeMap(): array {
        return [

        ];
    }

    protected function allByBuilder(QueryBuilder $queryBuilder): Collection
    {
        $postCollection = $queryBuilder->get();
        $array = $postCollection->toArray();

        $entityClass = $this->getEntityClass();
        return $this->getEntityManager()->createEntityCollection($entityClass, $array);
//        return EntityHelper::createEntityCollection($entityClass, $array);
    }

    /*public function getEntityClass(): string
    {
        return $this->entityClass;
    }*/

    /*protected function oneByBuilder(QueryBuilder $queryBuilder)
    {
        $item = $queryBuilder->first();
        if (empty($item)) {
            throw new NotFoundException('Not found entity!');
        }
        return $this->forgeEntity($item);
    }*/

}
