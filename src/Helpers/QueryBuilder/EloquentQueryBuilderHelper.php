<?php

namespace ZnLib\Db\Helpers\QueryBuilder;

use Illuminate\Database\Query\Builder;
use ZnCore\Domain\Entities\Query\Join;
use ZnLib\Db\Helpers\DbHelper;
use ZnLib\Db\Interfaces\QueryBuilderInterface;
use ZnCore\Domain\Libs\Query;
use ZnCore\Domain\Entities\Query\Where;

class EloquentQueryBuilderHelper implements QueryBuilderInterface
{

    public static function setWhere(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::WHERE])) {
            foreach ($queryArr[Query::WHERE] as $key => $value) {
                if (is_array($value)) {
                    $queryBuilder->whereIn($key, $value);
                } else {
                    $queryBuilder->where($key, $value);
                }
            }
        }

        $whereArray = $query->getWhereNew();
        if ( ! empty($whereArray)) {
            /** @var Where $where */
            foreach ($whereArray as $where) {
                if (is_array($where->value)) {
                    $queryBuilder->whereIn($where->column, $where->value, $where->boolean, $where->not);
                } else {
                    $queryBuilder->where($where->column, $where->operator, $where->value, $where->boolean);
                }
            }
        }
    }

    public static function setJoin(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr['join_new'])) {
            /** @var Join $join */
            foreach ($queryArr['join_new'] as $join) {
                $queryBuilder->join($join->table, $join->first, $join->operator, $join->second, $join->type, $join->where);
            }
        }
        if ( ! empty($queryArr[Query::JOIN])) {
            foreach ($queryArr[Query::JOIN] as $key => $value) {
                $queryBuilder->join($value['table'], $value['on'][0], '=', $value['on'][1], $value['type']);
            }
        }
    }

    public static function setOrder(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::ORDER])) {
            foreach ($queryArr[Query::ORDER] as $field => $direction) {
                $queryBuilder->orderBy($field, DbHelper::encodeDirection($direction));
            }
        }
    }

    public static function setGroupBy(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::GROUP])) {
            $queryBuilder->groupBy($queryArr[Query::GROUP]);
            /*foreach ($queryArr[Query::GROUP] as $field => $direction) {
                $queryBuilder->groupBy($field, DbHelper::encodeDirection($direction));
            }*/
        }
    }

    public static function setSelect(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::SELECT])) {
            $queryBuilder->select($queryArr[Query::SELECT]);
        }
    }

    public static function setPaginate(Query $query, Builder $queryBuilder)
    {
        $queryArr = $query->toArray();
        if ( ! empty($queryArr[Query::LIMIT])) {
            $queryBuilder->limit($queryArr[Query::LIMIT]);
        }
        if ( ! empty($queryArr[Query::OFFSET])) {
            $queryBuilder->offset($queryArr[Query::OFFSET]);
        }
    }

}