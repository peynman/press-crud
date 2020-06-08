<?php

namespace Larapress\CRUD\Validations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DBObjectIDsValidator
{
    public static function register()
    {
        Validator::extend('db_object_ids', function ($attributes, $values, $parameters, $validator) {
            if (is_string($values)) {
                $values = json_decode($values, true);
            }

            if (is_array($values) && count($parameters) === 3) {
                $ids = [];
                foreach ($values as $value) {
                    if (! isset($value[$parameters[1]])) {
                        return false;
                    } else {
                        $ids[] = $value[$parameters[1]];
                    }
                }
                $db_count = DB::table($parameters[0])->whereIn($parameters[2], $ids)->count();

                return $db_count === count($values);
            }

            return false;
        });
    }
}
