<?php

namespace Larapress\CRUD\Base;

abstract class SingleSourceBaseMetadata
{
    private static $instances = [];

    /**
     * @return mixed
     */
    public static function instance()
    {
        $class_name = get_called_class();
        if (! isset(self::$instances[$class_name])) {
            self::$instances[$class_name] = new $class_name;
        }

        return self::$instances[$class_name];
    }
}
