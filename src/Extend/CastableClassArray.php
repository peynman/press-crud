<?php

namespace Larapress\CRUD\Extend;

use Carbon\Carbon;
use stdClass;

abstract class CastableClassArray extends stdClass
{
    function __construct($payload)
    {
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
                    switch ($this->TYPE_CASTS[$attrName]) {
                        case 'boolean':
                        case 'bool':
                            $this->{$attrName} = boolval($uncastedValue);
                        case 'float':
                            $this->{$attrName} = floatval($uncastedValue);
                            break;
                        case 'int':
                            $this->{$attrName} = intval($uncastedValue);
                            break;
                        case 'carbon':
                            if (!is_null($uncastedValue)) {
                                $this->{$attrName} = Carbon::parse($uncastedValue);
                            } else {
                                $this->{$attrName} = null;
                            }
                            break;
                        default:
                            $this->{$attrName} = $uncastedValue;
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
        foreach (get_object_vars($this) as $attrName => $attrValue) {
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
