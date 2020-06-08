<?php

namespace Larapress\CRUD\Validations;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DateTimeZonedValidator
{
    public static function register()
    {
        Validator::extend('datetime_zoned', function ($attributes, $values, $parameters, $validator) {
            return true;
        });
    }
}
