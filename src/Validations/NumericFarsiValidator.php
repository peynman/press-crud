<?php

namespace Larapress\CRUD\Validations;

use Illuminate\Support\Facades\Validator;
use Larapress\CRUD\Extend\Helpers;

class NumericFarsiValidator
{
    public static function register()
    {
        Validator::extend('numeric_farsi', function ($attributes, $values, $parameters, $validator) {
            return is_numeric(Helpers::safeLatinNumbers($values));
        });
    }
}
