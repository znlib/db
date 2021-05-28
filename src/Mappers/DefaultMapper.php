<?php

namespace ZnLib\Db\Mappers;

use ZnCore\Base\Helpers\ClassHelper;
use ZnCore\Contract\Mapper\Interfaces\MapperInterface;
use ZnCore\Domain\Helpers\EntityHelper;

class DefaultMapper implements MapperInterface
{

    private $entityClass;

    public function __construct(string $entityClass)
    {
        $this->entityClass = $entityClass;
    }

    public function encode(object $entity): array
    {
        $data = EntityHelper::toArrayForTablize($entity);
        return $data;
    }

    public function decode(array $row): object
    {
        $entity = ClassHelper::createInstance($this->entityClass);
        EntityHelper::setAttributes($entity, $row);
        return $entity;
    }
}
