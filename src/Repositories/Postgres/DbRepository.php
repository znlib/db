<?php

namespace ZnLib\Db\Repositories\Postgres;

use App\Example\Controllers\ExampleEntity;
use Illuminate\Support\Collection;
use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnCore\Domain\Helpers\EntityHelper;
use ZnLib\Db\Entities\ColumnEntity;
use ZnLib\Db\Entities\RelationEntity;
use ZnLib\Db\Entities\TableEntity;

class DbRepository extends \ZnLib\Db\Repositories\Base\DbRepository
{

    public function allTables(): Collection
    {
        $connection = $this->getConnection();
        $schemas = $this->allSchemas();
        $tableCollection = new Collection;
        foreach ($schemas as $schemaName) {
            $tables = $connection->select("SELECT * FROM information_schema.tables WHERE table_schema = '{$schemaName}'");
            $tableNames = ArrayHelper::getColumn($tables, 'table_name');
            foreach ($tableNames as $tableName) {
                // $tableName = StructHelper::getTableNameFromEntity($tableEntity);
                $tableEntity = new TableEntity();
                $tableEntity->setName($tableName);
                $tableEntity->setSchemaName($schemaName);
                $tableEntity->setDbName($connection->getDatabaseName());
                $tableCollection->add($tableEntity);
            }
        }
        return $tableCollection;
    }

    public function allRelations(string $tableName): Collection
    {
        $sql = "SELECT
tc.constraint_name, tc.table_name, kcu.column_name, 
ccu.table_name AS foreign_table_name,
ccu.column_name AS foreign_column_name 
FROM 
information_schema.table_constraints AS tc 
JOIN information_schema.key_column_usage AS kcu
  ON tc.constraint_name = kcu.constraint_name
JOIN information_schema.constraint_column_usage AS ccu
  ON ccu.constraint_name = tc.constraint_name
WHERE constraint_type = 'FOREIGN KEY' AND tc.table_name='$tableName';";
        $array = $this->getConnection()->select($sql);
        $collection = new Collection();
        foreach ($array as $item) {
            $relationEntity = new RelationEntity();
            $relationEntity->setConstraintName($item->constraint_name);
            $relationEntity->setTableName($item->table_name);
            $relationEntity->setColumnName($item->column_name);
            $relationEntity->setForeignTableName($item->foreign_table_name);
            $relationEntity->setForeignColumnName($item->foreign_column_name);
            $collection->add($relationEntity);
        }
        return $collection;
    }

    private function allSchemas(): array
    {
        $connection = $this->getConnection();
        $schemaCollection = $connection->select("select schema_name from information_schema.schemata;");
        $schemaNames = ArrayHelper::getColumn($schemaCollection, 'schema_name');
        $excludes = [
            "pg_toast",
            "pg_temp_1",
            "pg_toast_temp_1",
            "pg_catalog",
            "information_schema",
        ];
        $schemaNames = array_diff($schemaNames, $excludes);
        return $schemaNames;
    }

    public function allColumnsByTable(string $tableName, string $schemaName = 'public'): Collection
    {
        $connection = $this->getConnection();
        /*$schemaCollection = $connection->select("SELECT column_name, data_type
FROM information_schema.columns
WHERE table_name = '$tableName' AND table_schema = '$schemaName';");

        $schemaCollection = $connection->select("SELECT
    pg_attribute.attname AS column_name,
    pg_catalog.format_type(pg_attribute.atttypid, pg_attribute.atttypmod) AS data_type
FROM
    pg_catalog.pg_attribute
INNER JOIN
    pg_catalog.pg_class ON pg_class.oid = pg_attribute.attrelid
INNER JOIN
    pg_catalog.pg_namespace ON pg_namespace.oid = pg_class.relnamespace
WHERE
    pg_attribute.attnum > 0
    AND NOT pg_attribute.attisdropped
    AND pg_namespace.nspname = '$schemaName'
    AND pg_class.relname = '$tableName'
ORDER BY
    attnum ASC;");*/


        $schemaCollection = $connection->select("
SELECT
    \"pg_attribute\".attname as \"name\", 
    pg_catalog.format_type(\"pg_attribute\".atttypid, \"pg_attribute\".atttypmod) as \"type\",
    not(\"pg_attribute\".attnotnull) AS \"nullable\"
FROM
    pg_catalog.pg_attribute \"pg_attribute\"
WHERE
    \"pg_attribute\".attnum > 0
    AND NOT \"pg_attribute\".attisdropped
    AND \"pg_attribute\".attrelid = (
        SELECT \"pg_class\".oid
        FROM pg_catalog.pg_class \"pg_class\"
            LEFT JOIN pg_catalog.pg_namespace \"pg_namespace\" ON \"pg_namespace\".oid = \"pg_class\".relnamespace
        WHERE
            \"pg_namespace\".nspname = '$schemaName'
            AND \"pg_class\".relname = '$tableName'
        ORDER BY \"pg_attribute\".attnum ASC
     );");


        $columnCollection = new Collection();
        foreach ($schemaCollection as $attribute) {
            $columnEntity = new ColumnEntity();

            $isMatch = preg_match('/(.+)\((\d+)\)/i', $attribute->type, $matches);
            if($isMatch) {
                $type = $matches[1];
                $length = intval($matches[2]);
            } else {
                $type = $attribute->type;
                $length = null;
            }

            $columnEntity->setName($attribute->name);
            $columnEntity->setType($type);
            $columnEntity->setLength($length);
            $columnEntity->setNullable($attribute->nullable);
            $columnCollection->add($columnEntity);
        }
        return $columnCollection;
    }
}
