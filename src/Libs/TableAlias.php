<?php

namespace ZnLib\Db\Libs;

use ZnCore\Base\Legacy\Yii\Helpers\ArrayHelper;

class TableAlias
{

    private $map = null;
    private $connectionMaps = [];

    public function addMap(string $connectionName, array $map)
    {
        $this->connectionMaps[$connectionName] = $map;
    }

    public function encode(string $connectionName, string $sourceTableName)
    {
        $map = $this->connectionMaps[$connectionName];
        $targetTableName = ArrayHelper::getValue($map, $sourceTableName, $sourceTableName);
        return $targetTableName;
    }

    public function decode(string $connectionName, string $targetTableName)
    {
        $map = $this->connectionMaps[$connectionName];
        $map = array_flip($map);
        $sourceTableName = ArrayHelper::getValue($map, $targetTableName, $targetTableName);
        return $sourceTableName;
    }

    public function getLocalName(string $tableName, array $map = null)
    {
        if (isset($map)) {
            $map = array_flip($map);
            $globalName = ArrayHelper::getValue($map, $tableName);
        } else {
            $this->loadMap();
            $map = array_flip($this->map);
            $globalName = ArrayHelper::getValue($map, $tableName);
        }
        if ($globalName) {
            $tableName = $globalName;
        }
        return $tableName;
    }

    public function getGlobalName(string $tableName, array $map = null)
    {
        if (isset($map)) {
            $globalName = ArrayHelper::getValue($map, $tableName);
        } else {
            $this->loadMap();
            $globalName = ArrayHelper::getValue($this->map, $tableName);
        }
        if ($globalName) {
            $tableName = $globalName;
        }
        return $tableName;
    }

    private function loadMap()
    {
        if ($this->map === null) {
            $config = EnvService::getConnection('main');
            if ($config['driver'] == 'pgsql') {
                $this->map = ArrayHelper::getValue($config, 'map', []);
            } else {
                $this->map = [];
            }
        }
    }
}