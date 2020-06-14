<?php

namespace Larapress\CRUD;

trait BaseType
{
    public static function toArray()
    {
        $minValue = self::MINVALUE;
        $maxValue = self::MAXVALUE;
        $json = [];
        for ($i = $minValue; $i <= $maxValue; $i++) {
            $json[] = [
                'id' => $i,
                'title' => self::getTitle($i),
            ];
        }

        return $json;
    }

    protected static function __getFlagProperty($flag, $transKey)
    {
        return trans($transKey.'.'.$flag);
    }
}
