<?php

namespace ZnLib\Db\Mappers;

use ZnCore\Base\Interfaces\EncoderInterface;

class GmpMapper implements EncoderInterface
{

    private $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
//        dd($this->attributes);
    }

    public function encode($data)
    {
        foreach ($this->attributes as $attribute) {
            $data[$attribute] = gmp_strval($data[$attribute], 16);

//            $binary = bin2hex();
//            $data[$attribute] = base64_encode($data[$attribute]);
        }
        return $data;
    }

    public function decode($row)
    {
        foreach ($this->attributes as $attribute) {
            $value = $row[$attribute] ?? null;
            if($value) {
//                dd($value);
                $row[$attribute] = gmp_init($row[$attribute], 16);
//                $row[$attribute] = base64_decode($row[$attribute]);
            }
        }
        return $row;
    }
}
