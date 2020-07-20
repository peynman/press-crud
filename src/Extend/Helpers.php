<?php

namespace Larapress\CRUD\Extend;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;

class Helpers
{
    public static function enNumberReplace($string)
    {
        $find = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $replace = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        return (string) str_replace($replace, $find, $string);
    }

    public static function randomId()
    {
        return rand(100000, 1000000);
    }

    public static function getBase64($path)
    {
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);

        return self::getBase64FromContent($data, $type);
    }

    public static function getBase64FromContent($content, $type)
    {
        return 'data:image/'.$type.';base64,'.base64_encode($content);
    }

    public static function gregDateString(Carbon $date, $format = 'Y-m-d')
    {
        return $date->format($format);
    }

    public static function randomNumbers($len = 8)
    {
        $numbers = '1234567890';
        $rnd = $numbers[rand(0, strlen($numbers) - 2)];
        for ($i = 1; $i < $len; $i++) {
            $rnd .= $numbers[rand(0, strlen($numbers) - 1)];
        }

        return $rnd;
    }

    public static function randomString($len = 5)
    {
        $numbers = 'qweuiopasdghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        $rnd = '';
        for ($i = 0; $i < $len; $i++) {
            $rnd .= $numbers[rand(0, strlen($numbers) - 1)];
        }

        return $rnd;
    }

    public static function inArrayRecursive($needle, $haystack)
    {
        $found = false;
        foreach ($haystack as $item) {
            if ($item === $needle) {
                $found = true;
                break;
            } elseif (is_array($item)) {
                $found = self::inArrayRecursive($needle, $item);
                if ($found) {
                    break;
                }
            }
        }

        return $found;
    }

    public static function inObjectRecursive($needle, $haystack)
    {
        $found = false;
        foreach ($haystack as $id => $item) {
            if ($item === $needle || $id === $needle) {
                $found = true;
                break;
            } elseif (is_array($item) || is_object($item)) {
                $found = self::inObjectRecursive($needle, $item);
                if ($found) {
                    break;
                }
            }
        }

        return $found;
    }

    public static function getTimezonesList()
    {
        $zoneNames = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezones = [];

        foreach ($zoneNames as $zone_name) {
            $offset = (new \DateTime(null, new DateTimeZone($zone_name)))->getOffset();
            if (! isset($timezones[$offset])) {
                $zoneOffset = CarbonInterval::create(0, 0, 0, 0, 0, 0, abs($offset))->cascade();
                $offsetHours = sprintf('%02d', intval($zoneOffset->hours));
                $offsetMinutes = sprintf('%02d', intval($zoneOffset->minutes));
                if ($offset < 0) {
                    $offsetHours = '-'.$offsetHours;
                } else {
                    $offsetHours = '+'.$offsetHours;
                }
                $timezones[$offset] = [
                    'regions' => [],
                    'display' => 'UTC '.$offsetHours.':'.$offsetMinutes,
                    'offset' => $offset,
                    'zone' => $offsetHours.$offsetMinutes,
                ];
            }
            $nameParts = explode('/', $zone_name);
            if (count($nameParts) == 2) {
                if (! isset($timezones[$offset]['regions'][$nameParts[0]])) {
                    $timezones[$offset]['regions'][$nameParts[0]] = [];
                }
                if (! in_array($nameParts[1], $timezones[$offset]['regions'][$nameParts[0]])) {
                    $timezones[$offset]['regions'][$nameParts[0]][] = $nameParts[1];
                }
            }
        }

        ksort($timezones);

        return $timezones;
    }

    public static function getArraySubsets($objects, $size)
    {
        $total = count($objects);
        $array = array_keys(array_fill(0, $total, 0));

        $subset = function ($array, $start_index, $depth, &$activeSet, &$output, $subsetter) use ($total) {
            if ($depth > 1) {
                $activeSet[] = $array[$start_index];
                for ($i = $start_index; $i < $total - 1; $i++) {
                    $copy = $activeSet;
                    $subsetter($array, $i + 1, $depth - 1, $copy, $output, $subsetter);
                }
            } else {
                $activeSet[] = $array[$start_index];
                $output[] = $activeSet;
                $activeSet = [];
            }
        };

        $output = [];
        for ($i = 0; $i < $total; $i++) {
            $empty = [];
            $subset($array, $i, $size, $empty, $output, $subset);
        }

        $total = count($output);
        for ($i = 0; $i < $total; $i++) {
            $iCount = count($output[$i]);
            for ($j = 0; $j < $iCount; $j++) {
                $output[$i][$j] = $objects[$output[$i][$j]];
            }
        }

        return $output;
    }

    public static function addQueryParamToUrl($url, $params)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            return $url.'&'.http_build_query($params);
        } else {
            return $url.'?'.http_build_query($params);
        }
    }

    public static function getNoSpace($string)
    {
        return str_replace(' ', '', $string);
    }

    public static function walkCSV($file, $pass)
    {
        $handle = fopen($file, 'r');
        if ($handle) {
            while ($content = fgetcsv($handle, 0, ',', '\'', "\n")) {
                $pass($content);
            }
            fclose($handle);
        }
    }

    public static function arrayMergeRecursive($arr1, $arr2)
    {
        foreach ($arr2 as $key => $value) {
            if (is_numeric($key)) {
                $arr1[] = $arr2[$key];
            } else {
                if (isset($arr1[$key]) && is_array($arr1[$key])) {
                    $arr1[$key] = self::arrayMergeRecursive($arr1[$key], $arr2[$key]);
                } else {
                    $arr1[$key] = $value;
                }
            }
        }

        return $arr1;
    }

    public static function getArrayWithPath($array, $path)
    {
        $steps = explode('.', $path);
        foreach ($steps as $step) {
            if (isset($array[$step])) {
                $array = $array[$step];
            } else {
                return null;
            }
        }

        return $array;
    }

    public static function getCachedValue($key, $callback, $tags, $ttl) {
        $result = Cache::get($key, null);
        if (is_null($result)) {
            $result = $callback();
            Cache::tags($tags)->put($key, $result, $ttl);
        }

        return $result;
    }

    public static function getPathWithoutExtension($path) {
        return substr($path, 0, strrpos($path, '.'));
    }
}
