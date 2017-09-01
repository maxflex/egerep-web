<?php
namespace App\Models\Service;

use Carbon\Carbon;
/**
 * Parser cache
 */
class ParserCache
{
    const CACHE_TIME = 30; // minutes

    public static function get($key)
    {
        if (\App::environment('local')) {
            return null;
        }
        if (in_array(static::getFunctionName($key), Parser::$cached_functions)) {
            return \Cache::get($key, false);
        }
    }

    public static function set($key, $value)
    {
        if (in_array(self::getFunctionName($key), Parser::$cached_functions)) {
            \Cache::put($key, $value, Carbon::now()->addMinutes(self::CACHE_TIME));
        }
    }

    private static function getFunctionName($key)
    {
        $args = explode('|', $key);
        return $args[0];
    }
}
