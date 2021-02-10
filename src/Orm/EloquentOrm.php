<?php

namespace ZnLib\Db\Orm;

use Illuminate\Support\Facades\DB;
use ZnCore\Domain\Interfaces\Libs\OrmInterface;
use ZnLib\Db\Capsule\Manager;

class EloquentOrm implements OrmInterface
{

    private $connection;

    public function __construct(Manager $connection)
    {
        $this->connection = $connection;
    }

    public function beginTransaction()
    {
        $this->connection->getDatabaseManager()->beginTransaction();
    }

    public function rollbackTransaction()
    {
        $this->connection->getDatabaseManager()->rollBack();
    }

    public function commitTransaction()
    {
        $this->connection->getDatabaseManager()->commit();
    }
}
