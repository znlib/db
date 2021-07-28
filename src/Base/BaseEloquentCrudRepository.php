<?php

namespace ZnLib\Db\Base;

use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use ZnCore\Base\Exceptions\AlreadyExistsException;
use ZnCore\Base\Exceptions\NotFoundException;
use ZnCore\Base\Helpers\DeprecateHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\Inflector;
use ZnCore\Base\Libs\Event\Traits\EventDispatcherTrait;
use ZnCore\Contract\Mapper\Interfaces\MapperInterface;
use ZnCore\Domain\Enums\EventEnum;
use ZnCore\Domain\Enums\OperatorEnum;
use ZnCore\Domain\Events\EntityEvent;
use ZnCore\Domain\Events\QueryEvent;
use ZnCore\Domain\Exceptions\UnprocessibleEntityException;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Helpers\FilterModelHelper;
use ZnCore\Domain\Helpers\QueryHelper;
use ZnCore\Domain\Helpers\ValidationHelper;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;
use ZnCore\Domain\Interfaces\Entity\UniqueInterface;
use ZnCore\Domain\Interfaces\ForgeQueryByFilterInterface;
use ZnCore\Domain\Interfaces\Repository\CrudRepositoryInterface;
use ZnCore\Domain\Libs\Query;
use ZnLib\Db\Helpers\QueryBuilder\EloquentQueryBuilderHelper;
use ZnLib\Db\Libs\QueryFilter;
use ZnLib\Db\Traits\MapperTrait;

abstract class BaseEloquentCrudRepository extends BaseEloquentRepository implements CrudRepositoryInterface, ForgeQueryByFilterInterface
{

    use EventDispatcherTrait;


    protected $primaryKey = ['id'];

    /*public function _relations()
    {
        return [];
    }*/

    public function primaryKey()
    {
        return $this->primaryKey;
    }

    protected function forgeQuery(Query $query = null)
    {
        $query = Query::forge($query);
        return $query;
    }

    public function forgeQueryByFilter(object $filterModel, Query $query = null)
    {
        FilterModelHelper::validate($filterModel);
        FilterModelHelper::forgeOrder($query, $filterModel);
        $query = $this->forgeQuery($query);
        $event = new QueryEvent($query);
        $event->setFilterModel($filterModel);
        $this->getEventDispatcher()->dispatch($event, EventEnum::BEFORE_FORGE_QUERY_BY_FILTER);
        FilterModelHelper::forgeCondition($query, $filterModel);
    }

    protected function queryFilterInstance(Query $query = null)
    {
        $query = $this->forgeQuery($query);
        /** @var QueryFilter $queryFilter */
        $queryFilter = new QueryFilter($this, $query);
        return $queryFilter;
    }

    public function count(Query $query = null): int
    {
        $query = $this->forgeQuery($query);
        $queryBuilder = $this->getQueryBuilder();
        EloquentQueryBuilderHelper::setWhere($query, $queryBuilder);
        EloquentQueryBuilderHelper::setJoin($query, $queryBuilder);
        return $queryBuilder->count();
    }

    public function _all(Query $query = null)
    {
        $query = $this->forgeQuery($query);
        $queryBuilder = $this->getQueryBuilder();
        $query->select([$queryBuilder->from . '.*']);
        EloquentQueryBuilderHelper::setWhere($query, $queryBuilder);
        EloquentQueryBuilderHelper::setJoin($query, $queryBuilder);
        EloquentQueryBuilderHelper::setSelect($query, $queryBuilder);
        EloquentQueryBuilderHelper::setOrder($query, $queryBuilder);
        EloquentQueryBuilderHelper::setGroupBy($query, $queryBuilder);
        EloquentQueryBuilderHelper::setPaginate($query, $queryBuilder);
        $collection = $this->allByBuilder($queryBuilder);
        return $collection;
    }

    public function all(Query $query = null)
    {
        $query = $this->forgeQuery($query);
        $queryFilter = $this->queryFilterInstance($query);
        $queryWithoutRelations = $queryFilter->getQueryWithoutRelations();
        $collection = $this->_all($queryWithoutRelations);
        $queryFilter->loadRelations($collection);
        return $collection;
    }

    public function loadRelations(Collection $collection, array $with) {
        $query = $this->forgeQuery();
        $query->with($with);
        $queryFilter = $this->queryFilterInstance($query);
        $queryFilter->loadRelations($collection);
    }

    public function oneById($id, Query $query = null): EntityIdInterface
    {
        $query = $this->forgeQuery($query);
        $query->where($this->primaryKey[0], $id);
        return $this->one($query);
    }

    public function one(Query $query = null)
    {
        $query->limit(1);
        $collection = $this->all($query);
        if ($collection->count() < 1) {
            throw new NotFoundException('Not found entity!');
        }
        return $collection->first();
    }

    public function create(EntityIdInterface $entity)
    {
        ValidationHelper::validateEntity($entity);
        /*$existedEntity = $this->oneByUnique($entity);
        if($existedEntity) {
            throw new AlreadyExistsException(get_class($entity) . ' already exist!');
        }*/
        $arraySnakeCase = $this->mapperEncodeEntity($entity);
        $queryBuilder = $this->getQueryBuilder();
        try {
            $lastId = $queryBuilder->insertGetId($arraySnakeCase);
            $entity->setId($lastId);
        } catch (QueryException $e) {
            $errors = new UnprocessibleEntityException;
            if($_ENV['APP_DEBUG']) {
                $message = $e->getMessage();
                $message = preg_replace('/(\s+)/i', ' ', $message);
                $message = str_replace("'", "\\'", $message);
                $message = trim($message);
            } else {
                $message = 'Database error!';
            }
            $errors->add('', $message);
            throw $errors;
        }
    }

    public function oneByUnique(UniqueInterface $entity): EntityIdInterface
    {
//        $entityClass = get_class($entity);
        $unique = $entity->unique();
        foreach ($unique as $uniqueConfig) {
            $query = new Query();
            foreach ($uniqueConfig as $uniqueName) {
                $query->where(Inflector::underscore($uniqueName), EntityHelper::getValue($entity, $uniqueName));
            }
            $all = $this->all($query);
            if ($all->count() > 0) {
                return $all->first();
                //EntityHelper::setAttributes($entity, EntityHelper::toArray($all->first()));
                //return;
            }
        }
        throw new NotFoundException();
    }

    public function createCollection(Collection $collection)
    {
        DeprecateHelper::softThrow();
        $array = [];
        foreach ($collection as $entity) {
            ValidationHelper::validateEntity($entity);
            $columnList = $this->getColumnsForModify();
            $array[] = EntityHelper::toArrayForTablize($entity, $columnList);
        }
        $this->getQueryBuilder()->insert($array);
    }

    protected function getColumnsForModify()
    {
        $schema = $this->getSchema();
        $columnList = $schema->getColumnListing($this->tableNameAlias());
        if (empty($columnList)) {
            $columnList = EntityHelper::getAttributeNames($this->getEntityClass());
            foreach ($columnList as &$item) {
                $item = Inflector::underscore($item);
            }

        }
        if ($this->autoIncrement()) {
            ArrayHelper::removeByValue($this->autoIncrement(), $columnList);
        }
        return $columnList;
    }

    /*public function persist(EntityIdInterface $entity)
    {

    }*/

    public function update(EntityIdInterface $entity)
    {
        ValidationHelper::validateEntity($entity);
        $this->oneById($entity->getId());
        $data = $this->mapperEncodeEntity($entity);
        $this->updateQuery($entity->getId(), $data);
        //$this->updateById($entity->getId(), $data);
    }

    /*public function updateById($id, $data)
    {
        $this->oneById($id);
        $this->updateQuery($id, $data);
    }*/

    private function updateQuery($id, array $data)
    {
        $columnList = $this->getColumnsForModify();
        $data = ArrayHelper::extractByKeys($data, $columnList);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->find($id);
        $queryBuilder->update($data);
    }

    public function deleteById($id)
    {
        $this->oneById($id);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->delete($id);
    }

    public function updateByQuery(Query $query, array $values)
    {
        $query = $this->forgeQuery($query);
        $queryFilter = $this->queryFilterInstance($query);
        $queryWithoutRelations = $queryFilter->getQueryWithoutRelations();
//        $collection = $this->_all($queryWithoutRelations);
        $query = $this->forgeQuery($query);
        $queryBuilder = $this->getQueryBuilder();
        $query->select([$queryBuilder->from . '.*']);
        EloquentQueryBuilderHelper::setWhere($query, $queryBuilder);
        EloquentQueryBuilderHelper::setJoin($query, $queryBuilder);
        EloquentQueryBuilderHelper::setSelect($query, $queryBuilder);
        EloquentQueryBuilderHelper::setOrder($query, $queryBuilder);
        EloquentQueryBuilderHelper::setGroupBy($query, $queryBuilder);
        EloquentQueryBuilderHelper::setPaginate($query, $queryBuilder);
        $queryBuilder->update($values);
//        $collection = $this->allByBuilder($queryBuilder);
//        return $collection;
    }

    public function deleteByCondition(array $condition)
    {
        $queryBuilder = $this->getQueryBuilder();
        foreach ($condition as $key => $value) {
            $queryBuilder->where($key, OperatorEnum::EQUAL, $value);
        }
        $queryBuilder->delete();
    }
}
