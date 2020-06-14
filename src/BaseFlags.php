<?php

namespace Larapress\CRUD;

trait BaseFlags
{
    public static function toArray()
    {
        $maxValue = self::MAXVALUE;
        $json = [];
        for ($i = 1; $i <= $maxValue; $i *= 2) {
            $json[] = [
                'id'    => $i,
                'title' => self::getTitle($i),
            ];
        }

        return $json;
    }

    public static function isActive($flags, $flag)
    {
        return ($flags & $flag) != 0;
    }

    protected static function __getFlagProperty($flag, $transKey, $property = 'title')
    {
        $trans = trans($transKey.'.'.$flag);
        if (is_array($trans) && isset($trans[$property])) {
            return $trans[$property];
        }

        return $trans;
    }
}
