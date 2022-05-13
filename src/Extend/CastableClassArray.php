<?php

namespace Larapress\CRUD\Extend;

use Carbon\Carbon;
use stdClass;
use Illuminate\Support\Str;

abstract class CastableClassArray extends stdClass
{
    protected $TYPE_CASTS = [];

    function __construct($payload, $casts = [])
    {
        $this->TYPE_CASTS = array_merge($this->TYPE_CASTS, $casts);
        if (is_array($payload)) {
            $this->from_array($payload);
        }
    }

    public function from_array($array)
    {
        foreach (get_object_vars($this) as $attrName => $attrValue) {
            if (isset($array[$attrName])) {
                $uncastedValue = $array[$attrName];
                if (isset($this->TYPE_CASTS[$attrName])) {
                    if (is_null($uncastedValue)) {
                        $this->{$attrName} = null;
                        continue;
                    }

                    switch ($this->TYPE_CASTS[$attrName]) {
                        case 'boolean':
                        case 'bool':
                            if (gettype($uncastedValue) === 'boolean') {
                                $this->{$attrName} = $uncastedValue;
                            } else {
                                $this->{$attrName} = boolval($uncastedValue);
                            }
                        case 'float':
                        case 'decimal':
                            $this->{$attrName} = floatval($uncastedValue);
                            break;
                        case 'int':
                        case 'integer':
                        case 'number':
                            $this->{$attrName} = intval($uncastedValue);
                            break;
                        case 'date':
                        case 'carbon':
                            $this->{$attrName} = Carbon::parse($uncastedValue);
                            break;
                        default:
                            if (Str::startsWith($this->TYPE_CASTS[$attrName], 'array:')) {
                                $this->{$attrName} = [];
                                $arrItemClass = Str::substr($this->TYPE_CASTS[$attrName], Str::length('array:'));
                                foreach ($uncastedValue as $uncastedArrItem) {
                                    $this->{$attrName}[] = new $arrItemClass($uncastedArrItem);
                                }
                            } else if (Str::startsWith($this->TYPE_CASTS[$attrName], 'object:')) {
                                $arrItemClass = Str::substr($this->TYPE_CASTS[$attrName], Str::length('object:'));
                                $this->{$attrName} = new $arrItemClass($uncastedValue);
                            } else {
                                $this->{$attrName} = $uncastedValue;
                            }
                    }
                } else {
                    $this->{$attrName} = $uncastedValue;
                }
            }
        }
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public function toArray()
    {
        $array = [];
        $self = get_object_vars($this);
        foreach ($self as $attrName => $attrValue) {
            if ($attrName === 'TYPE_CASTS') {
                continue;
            }

            if (isset($this->TYPE_CASTS[$attrName])) {
                switch ($this->TYPE_CASTS[$attrName]) {
                    case 'carbon':
                        $value = $this->{$attrName};
                        if (!is_null($value)) {
                            $array[$attrName] = $value->format(config('larapress.crud.datetime-format'));
                        } else {
                            $array[$attrName] = null;
                        }
                        break;
                    default:
                        $array[$attrName] = $this->{$attrName};
                }
            } else {
                $array[$attrName] = $this->{$attrName};
            }
        }

        return $array;
    }
}
