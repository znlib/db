<?php

namespace ZnLib\Db\Mappers;

use ZnCore\Base\Interfaces\EncoderInterface;

class GmpMapper implements EncoderInterface
{

    private $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;

    }

    public function encode($data)
    {
        foreach ($this->attributes as $attribute) {
            $hex = gmp_strval($data[$attribute], 16);
            $binary = hex2bin($hex);
            $data[$attribute] = base64_encode($binary);
        }
        return $data;
    }

    public function decode($row)
    {
        foreach ($this->attributes as $attribute) {
            $value = $row[$attribute] ?? null;
            if ($value) {
                $binary = base64_decode($row[$attribute]);
                $hex = bin2hex($binary);
                $row[$attribute] = gmp_init($hex, 16);
            }
        }
        return $row;
    }
}
