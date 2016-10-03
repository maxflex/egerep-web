<?php

namespace App\Models\Service;
use DB;
use Cache;

class Factory
{
    public static function get($table, $key = 'id', $select = null)
    {
        $query = DB::connection('factory')->table($table);

        if ($select) {
            $query->select('id', $select);
        }

        $return = $query->get();

        if ($key) {
            $return = array_combine($return->pluck($key)->all(), $return->all());
        }

        return $return;
    }

    public static function json($table, $key = 'id', $select = null)
    {
        $data = static::get($table, $key, $select);
        return json_encode($data);
    }

    public static function constant($name)
    {
        $key = "constants:$name";
        if (Cache::has($key)) {
            return Cache::get($key);
        } else {
            $value = DB::connection('factory')->table('constants')->where('name', $name)->value('value');
            Cache::forever($key, $value);
            return $value;
        }
    }

    public static function getSubjectId($subject_eng)
    {
        return dbFactory('subjects')->where('eng', $subject_eng)->value('id');
    }
}
