<?php

namespace ZnLib\Db\Mappers;

use ZnCore\Base\Interfaces\EncoderInterface;
use ZnCore\Base\Legacy\Yii\Helpers\Inflector;

class TimeMapper implements EncoderInterface
{

    public $format = 'Y-m-d H:i:s';
    private $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function encode($data)
    {
        foreach ($this->attributes as $attribute) {
//            $data[$attribute] = $time->format($this->format);
//            $data[$attribute] = json_encode($data[$attribute], JSON_UNESCAPED_UNICODE);
        }

        return $data;
    }

    public function decode($row)
    {
        foreach ($this->attributes as $attribute) {
            $attribute = Inflector::underscore($attribute);
            $row[$attribute] = new \DateTime($row[$attribute]);
        }
        return $row;
    }
}
