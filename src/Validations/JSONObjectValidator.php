<?php

namespace Larapress\CRUD\Validations;

use Illuminate\Support\Facades\Validator;

class JSONObjectValidator
{
    public static function register()
    {
        Validator::extend('json_object', function ($attributes, $values, $parameters, $validator) {
            return is_array($values);
        });
    }
}
