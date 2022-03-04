<?php

namespace ZnLib\Db\Libs;

use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;
use ZnSandbox\Sandbox\Generator\Domain\Repositories\Eloquent\SchemaRepository;

class Dependency
{

    private $tableMap = [];
    private $tableQueue = [];
    private $processedTables = [];
    private $schemaRepository;

    public function __construct(SchemaRepository $schemaRepository)
    {
        $this->schemaRepository = $schemaRepository;
    }

    public function run($tables)
    {
        $this->tableMap = $this->getTableMap($tables);
        $this->tableQueue = [];
        foreach ($this->tableMap as $tableName => $tableInfo) {
            $this->processTable($tableName);
        }
        return $this->tableQueue;
    }

    protected function getTableMap($tables): array
    {
        $tableMap = [];
        $tableList = $this->schemaRepository->allTablesByName($tables);
        foreach ($tableList as $tableEntity) {
            $tableName = $tableEntity->getName();
            if ($tableEntity->getRelations()) {
                $deps = [];
                foreach ($tableEntity->getRelations() as $relationEntity) {
                    $deps[] = $relationEntity->getForeignTableName();
                }
            }
            $tableMap[$tableName] = [
//                'name' => $tableName,
                'deps' => array_values(array_unique($deps)),
            ];
        }
        return $tableMap;
    }

    protected function processTable($tableName)
    {
        if (in_array($tableName, $this->processedTables)) {
            return;
        }
        $this->processedTables[] = $tableName;
        $deps = ArrayHelper::getValue($this->tableMap, [$tableName, 'deps']);
        if ($deps) {
            foreach ($deps as $dep) {
                $this->processTable($dep);
            }
        }
        $this->tableQueue[] = $tableName;
    }
}
