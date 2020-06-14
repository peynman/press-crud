<?php

namespace Larapress\Core\Validations;

use Illuminate\Support\Facades\Validator;

class ValidateMinIf
{
    public static function register()
    {
        Validator::extend('min_if', function ($attributes, $values, $parameters, $validator) {
            if (count($parameters) !== 3) {
                return false;
            }

            $data = $validator->getData();
            if (isset($data[$parameters[0]]) && $data[$parameters[0]] == $parameters[1]) {
                $data[$attributes] = intval($values);
                $min_rule = [$attributes => 'numeric|min:'.$parameters[2]];
                $min_data = [$attributes => $data[$attributes]];
                $v = Validator::make($min_data, $min_rule);

                return ! $v->fails();
            }

            return true;
        });

        Validator::replacer('min_if', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':min', $parameters[2], $message);
        });
    }
}
