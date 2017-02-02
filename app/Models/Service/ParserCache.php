<?php
namespace App\Models\Service;

use Carbon\Carbon;
/**
 * Parser cache
 */
class ParserCache
{
    public static function get($key)
    {
        if (in_array(static::parseKey($key), Parser::$cached_functions)) {
            return \Cache::get($key, false);
        }
    }

    public static function set($key, $value, $ignore_cache = false)
    {
        if (in_array(self::parseKey($key), Parser::$cached_functions) && !$ignore_cache) {
            \Cache::put($key, $value, Carbon::now()->addMinutes(30));
        }
    }

    private static function parseKey($key)
    {
        $args = explode('|', $key);
        return $args[0];
    }
}
