<?php

namespace ZnLib\Db\Traits;

use Illuminate\Support\Collection;
use ZnCore\Base\Encoders\AggregateEncoder;
use ZnCore\Base\Helpers\ClassHelper;
use ZnCore\Base\Helpers\InstanceHelper;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Base\Legacy\Yii\Helpers\Inflector;
use ZnCore\Contract\Mapper\Interfaces\MapperInterface;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;
use ZnLib\Db\Mappers\DefaultMapper;

trait MapperTrait
{

    /**
     * @return MapperInterface
     * @deprecated
     */
    public function mapper(): MapperInterface
    {
        return new DefaultMapper($this->getEntityClass());
    }

    public function mappers(): array
    {
        return [

        ];
    }

    protected function underscore(array $attributes, array $columnList = []) {
        $arraySnakeCase = [];
        foreach ($attributes as $name => $value) {
            $tableizeName = Inflector::underscore($name);
            $arraySnakeCase[$tableizeName] = $value;
        }
        if ($columnList) {
            $arraySnakeCase = ArrayHelper::extractByKeys($arraySnakeCase, $columnList);
        }
        return $arraySnakeCase;
    }

    protected function mapperEncodeEntity(EntityIdInterface $entity): array
    {
        $mapper = $this->mapper();

        if($mapper instanceof DefaultMapper) {
            $attributes = EntityHelper::toArray($entity);
        } else {
            $attributes = $mapper->encode($entity);
        }

        $attributes = $this->underscore($attributes);

        $mappers = $this->mappers();
        if($mappers) {
            $encoders = new AggregateEncoder(new Collection($mappers));
            $attributes = $encoders->encode($attributes);
        }

        $columnList = $this->getColumnsForModify();
        $attributes = ArrayHelper::extractByKeys($attributes, $columnList);
        return $attributes;
    }

    protected function mapperDecodeEntity(array $array): object
    {
        $mapper = $this->mapper();

        if($mapper instanceof DefaultMapper) {

        } else {
            $entity = $mapper->decode($array);
        }

        $mappers = $this->mappers();
        if($mappers) {
            $encoders = new AggregateEncoder(new Collection($mappers));
            $array = $encoders->decode($array);
        }

        if(empty($entity)) {
            $entity = ClassHelper::createInstance($this->getEntityClass());
            EntityHelper::setAttributes($entity, $array);
        }

        //$entity = $mapper->decode($array);
        return $entity;
    }

    protected function mapperDecodeCollection(array $array): Collection
    {

        $collection = new Collection();
        foreach ($array as $item) {
            $entity = $this->mapperDecodeEntity((array)$item);
            $collection->add($entity);
        }
        return $collection;
    }
}
