<?php

namespace Larapress\CRUD\Extend;

use Carbon\CarbonInterval;
use DateTimeZone;
use Illuminate\Support\Facades\Cache;

class Helpers
{
    /**
     * Undocumented function
     *
     * @param string $string
     * @return string
     */
    public static function safeLatinNumbers($string)
    {
        $find = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $replace = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

        return (string) str_replace($replace, $find, $string);
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    public static function randomId()
    {
        return rand(100000, 1000000);
    }

    /**
     * Undocumented function
     *
     * @param integer $len
     * @return string
     */
    public static function randomNumbers($len = 8)
    {
        $numbers = '1234567890';
        $rnd = $numbers[rand(0, strlen($numbers) - 2)];
        for ($i = 1; $i < $len; $i++) {
            $rnd .= $numbers[rand(0, strlen($numbers) - 1)];
        }

        return $rnd;
    }

    /**
     * Undocumented function
     *
     * @param integer $len
     * @return string
     */
    public static function randomString($len = 5)
    {
        $numbers = 'qweuiopasdghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        $rnd = '';
        for ($i = 0; $i < $len; $i++) {
            $rnd .= $numbers[rand(0, strlen($numbers) - 1)];
        }

        return $rnd;
    }

    /**
     * Undocumented function
     *
     * @param string $path
     * @return string
     */
    public static function getPathWithoutExtension($path)
    {
        return substr($path, 0, strrpos($path, '.'));
    }

    /**
     * Undocumented function
     *
     * @param mixed $needle
     * @param array $haystack
     * @return bool
     */
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

    /**
     * Undocumented function
     *
     * @param mixed $needle
     * @param array $haystack
     * @return bool
     */
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

    /**
     * Undocumented function
     *
     * @param array $nested
     * @return array
     */
    public static function flattenNestedAray($arr)
    {
        $flatten = [];
        foreach ($arr as $item) {
            if (is_array($item)) {
                $flatten = array_merge($flatten, self::flattenNestedAray($item));
            } else {
                $flatten[] = $item;
            }
        }
        return $flatten;
    }

    /**
     * Undocumented function
     *
     * @return array
     */
    public static function getTimezonesList()
    {
        $zoneNames = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
        $timezones = [];

        foreach ($zoneNames as $zone_name) {
            $offset = (new \DateTime('now', new DateTimeZone($zone_name)))->getOffset();
            if (!isset($timezones[$offset])) {
                $zoneOffset = CarbonInterval::create(0, 0, 0, 0, 0, 0, abs($offset))->cascade();
                $offsetHours = sprintf('%02d', intval($zoneOffset->hours));
                $offsetMinutes = sprintf('%02d', intval($zoneOffset->minutes));
                if ($offset < 0) {
                    $offsetHours = '-' . $offsetHours;
                } else {
                    $offsetHours = '+' . $offsetHours;
                }
                $timezones[$offset] = [
                    'regions' => [],
                    'display' => 'UTC ' . $offsetHours . ':' . $offsetMinutes,
                    'offset' => $offset,
                    'zone' => $offsetHours . $offsetMinutes,
                ];
            }
            $nameParts = explode('/', $zone_name);
            if (count($nameParts) == 2) {
                if (!isset($timezones[$offset]['regions'][$nameParts[0]])) {
                    $timezones[$offset]['regions'][$nameParts[0]] = [];
                }
                if (!in_array($nameParts[1], $timezones[$offset]['regions'][$nameParts[0]])) {
                    $timezones[$offset]['regions'][$nameParts[0]][] = $nameParts[1];
                }
            }
        }

        ksort($timezones);

        return $timezones;
    }

    /**
     * Undocumented function
     *
     * @param array $objects
     * @param int $size
     * @return array
     */
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

    /**
     * Undocumented function
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    public static function addQueryParamToUrl($url, $params)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        if ($query) {
            return $url . '&' . http_build_query($params);
        } else {
            return $url . '?' . http_build_query($params);
        }
    }

    /**
     * Undocumented function
     *
     * @param string $string
     * @return string
     */
    public static function getNoSpace($string)
    {
        return str_replace(' ', '', $string);
    }

    /**
     * Undocumented function
     *
     * @param string $file
     * @param callable $pass
     * @return void
     */
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

    /**
     * Undocumented function
     *
     * @param array $arr1
     * @param array $arr2
     * @return array
     */
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

    /**
     * Undocumented function
     *
     * @param array $array
     * @param string $path
     * @return mixed
     */
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

    protected static $inMemoryCache = [];
    /**
     * Undocumented function
     *
     * @param string $key
     * @param callable $callback
     * @param array $tags
     * @param int $ttl
     * @return mixed
     */
    public static function getCachedValue(string $key, array $tags, int $ttl, bool $keepInMemory, callable $callback)
    {
        if (isset(self::$inMemoryCache[$key])) {
            return self::$inMemoryCache[$key];
        }

        $result = Cache::tags($tags)->get($key, null);
        if (is_null($result)) {
            $result = $callback();
            Cache::tags($tags)->put($key, $result, $ttl);
        }

        if ($keepInMemory) {
            self::$inMemoryCache[$key] = $result;
        }

        return $result;
    }

    /**
     * Undocumented function
     *
     * @param array $tags
     * @return void
     */
    public static function forgetCachedValues(array $tags)
    {
        Cache::tags($tags)->flush();
        self::$inMemoryCache = [];
    }

    /**
     * Undocumented function
     *
     * @param mixed $var
     * @return boolean
     */
    public static function isAssocArray($var)
    {
        return is_array($var) && array_diff_key($var, array_keys(array_keys($var)));
    }
}
