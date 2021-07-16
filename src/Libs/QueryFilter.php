<?php

namespace ZnLib\Db\Libs;

use Illuminate\Support\Collection;
use ZnBundle\Eav\Domain\Repositories\Eloquent\FieldRepository;
use ZnCore\Base\Helpers\DeprecateHelper;
use ZnCore\Domain\Libs\Query;
use ZnCore\Domain\Helpers\Repository\RelationHelper;
use ZnCore\Domain\Helpers\Repository\RelationWithHelper;
use ZnCore\Domain\Interfaces\ReadAllInterface;
use ZnCore\Domain\Interfaces\Repository\RelationConfigInterface;
use ZnCore\Domain\Relations\libs\RelationLoader;

class QueryFilter
{

    /**
     * @var RelationConfigInterface
     */
    private $repository;
    private $query;
    private $with;

    public function __construct(ReadAllInterface $repository, Query $query)
    {
        $this->repository = $repository;
        if ($this->repository && $this->repository instanceof RelationConfigInterface) {
            DeprecateHelper::softThrow('RelationConfigInterface is deprecated, use relations2 for definition!');
        }
        $this->query = $query;
    }

    public function getQueryWithoutRelations(): Query
    {
        $query = clone $this->query;
        if(method_exists($this->repository, 'relations')) {
            DeprecateHelper::softThrow('Method relations is deprecated, use relations2 for definition!');
            $this->with = RelationWithHelper::cleanWith($this->repository->relations(), $query);
        }
        return $query;
    }

    public function loadRelations(Collection $collection)
    {
        if(method_exists($this->repository, 'relations2')) {
            $relationLoader = new RelationLoader;
            $relationLoader->setRelations($this->repository->relations2());
            $relationLoader->setRepository($this->repository);
            $relationLoader->loadRelations($collection, $this->query);
            return $collection;
        }

        if (empty($this->with)) {
            return $collection;
        }
        /*if($this->repository instanceof FieldRepository) {
            prr($with);
        }*/
        $collection = RelationHelper::load($this->repository, $this->query, $collection);
        return $collection;
    }
}
