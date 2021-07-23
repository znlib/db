<?php

namespace ZnLib\Db\Mappers;

use ZnCore\Base\Interfaces\EncoderInterface;

class JsonMapper implements EncoderInterface
{

    private $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function encode($data)
    {
        foreach ($this->attributes as $attribute) {
            $data[$attribute] = json_encode($data[$attribute], JSON_UNESCAPED_UNICODE);
        }

        return $data;
    }

    public function decode($row)
    {
        foreach ($this->attributes as $attribute) {
            $row[$attribute] = json_decode($row[$attribute], JSON_OBJECT_AS_ARRAY);
        }
        return $row;
    }
}
