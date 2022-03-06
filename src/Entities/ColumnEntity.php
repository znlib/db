<?php

namespace ZnLib\Db\Entities;

use ZnCore\Base\Libs\App\Base\BaseBundle;

class ColumnEntity
{

    protected $name;
    protected $type;
    protected $length;
    protected $nullable;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type): void
    {
        $this->type = $type;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function setLength($length): void
    {
        $this->length = $length;
    }

    public function getNullable()
    {
        return $this->nullable;
    }

    public function setNullable($nullable): void
    {
        $this->nullable = $nullable;
    }
}
