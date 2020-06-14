<?php

namespace Larapress\CRUD\Validations;

use Illuminate\Support\Facades\Validator;

class NumericFarsiValidator
{
    public static function register()
    {
        Validator::extend('numeric_farsi', function ($attributes, $values, $parameters, $validator) {
            $values = Helpers::enNumbers($values);

            return is_numeric($values);
        });
    }
}
