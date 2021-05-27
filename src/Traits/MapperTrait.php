<?php

namespace ZnLib\Db\Traits;

use Illuminate\Support\Collection;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Contract\Mapper\Interfaces\MapperInterface;
use ZnCore\Domain\Interfaces\Entity\EntityIdInterface;
use ZnLib\Db\Mappers\DefaultMapper;

trait MapperTrait
{

    public function mapper(): MapperInterface
    {
        return new DefaultMapper($this->getEntityClass());
    }

    protected function mapperEncodeEntity(EntityIdInterface $entity): array
    {
        $columnList = $this->getColumnsForModify();
        $mapper = $this->mapper();
        $arraySnakeCase = $mapper->encode($entity);
        $arraySnakeCase = ArrayHelper::extractByKeys($arraySnakeCase, $columnList);
        return $arraySnakeCase;
    }

    protected function mapperDecodeCollection(array $array): Collection
    {
        $mapper = $this->mapper();
        $collection = new Collection();
        foreach ($array as $item) {
            $entity = $mapper->decode((array)$item);
            $collection->add($entity);
        }
        return $collection;
    }
}
