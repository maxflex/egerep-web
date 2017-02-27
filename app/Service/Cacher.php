<?php

namespace App\Service;
use Illuminate\Support\Facades\Redis;
use Cache;

/**
 * Увеличить скорость
 */
class Cacher
{
    const ONE_DAY = 1440; // minutes

    public static function getSubjectName($subject_id, $case)
    {
        return Cache::remember("subjects:{$subject_id}:{$case}", self::ONE_DAY, function() use ($subject_id, $case) {
            return dbFactory('subjects')->whereId($subject_id)->value($case);
        });
    }
}
