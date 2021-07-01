<?php

namespace Larapress\CRUD\Validations;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Validator;

class DateTimeZonedValidator
{
    public static function register()
    {
        Validator::extend('datetime_zoned', function ($attributes, $values, $parameters, $validator) {
            try {
                $datetime = Carbon::parse($values);
                return !is_null($datetime);
            } catch (Exception $e) {}

            return false;
        });
    }
}
