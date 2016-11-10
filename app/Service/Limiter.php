<?php

namespace App\Service;
use Illuminate\Support\Facades\Redis;
use App\Models\Service\Sms;
/**
 * Ограничить обращения
 */
class Limiter
{
    public static function run($key, $hours, $max, $success, $fail = null, $sms_text = null)
    {
        $key = "egerep:{$key}:count";
        $count = intval(Redis::get($key));
        if ($count < $max) {
            $return = $success();
            Redis::incr($key);
            if ($count == 1) {
                Redis::expire($key, 3600 * $hours);
            }
            return $return;
        } else {
            if ($count == $max && $sms_text !== null) {
                Sms::sendToAdmins($sms_text);
            }
            if (is_callable($fail)) {
                $fail();
            }
            return abort(403);
        }
    }
}
