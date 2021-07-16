<?php

namespace ZnLib\Db\Libs;

use Illuminate\Support\Collection;
use ZnBundle\Eav\Domain\Repositories\Eloquent\FieldRepository;
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
        $this->query = $query;
    }

    public function getQueryWithoutRelations(): Query
    {
        $query = clone $this->query;
        $this->with = RelationWithHelper::cleanWith($this->repository->relations(), $query);
        return $query;
    }

    public function loadRelations(Collection $collection)
    {
        if(method_exists($this->repository, 'relations2')) {
            $relationLoader = new RelationLoader;
            $relationLoader->setRelations($this->repository->relations2());
            $relationLoader->setRepository($this->repository);

            /*$with = $this->query->getParam(Query::WITH);
            if($with) {
                dd($with);
            }*/

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
        
        //dd($collection);
        return $collection;
    }

    /*public function getQuery() : Query {
        if(!isset($this->query)) {
            $this->query = Query::forge();
        }
        return $this->query;
    }
    
    public function setQuery(Query $query) {
        $this->query = clone $query;
    }*/

}
