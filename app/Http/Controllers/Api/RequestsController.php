<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Service\Api;
use App\Models\Service\Sms;
use Illuminate\Support\Facades\Redis;

class RequestsController extends Controller
{
    const REDIS_REQUEST_COUNT   = 'egerep:request:count';
    const REDIS_REQUEST_BLOCKED = 'egerep:request:blocked';
    const MAX_COMMENT_LENGTH    = 1000;
    const MAX_NAME_LENGTH       = 60;
    const REQUEST_MAX_COUNT     = 200;
    const REQUEST_FLUSH_TIME    = 3600 * 24;

    /**
     *
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'phone'   => ['required', 'size:18', 'regex:' . PHONE_REGEX],
            'name'    => [
                'regex:' . TEXT_VALIDATION_REGEX,
                'max:' . self::MAX_NAME_LENGTH,
            ],
            'comment' => [
                'regex:' . TEXT_VALIDATION_REGEX,
                'max:' . self::MAX_COMMENT_LENGTH,
            ]
        ]);

        $egerep_request_count = intval(Redis::get(self::REDIS_REQUEST_COUNT));

        // не более 200 за последние 24 часа
        if ($egerep_request_count < self::REQUEST_MAX_COUNT) {
            $_SESSION['sent_ids'][] = $request->tutor_id;
            Api::exec('requestNew', $request->input());
            Redis::incr(self::REDIS_REQUEST_COUNT);
            Redis::expire(self::REDIS_REQUEST_COUNT, self::REQUEST_FLUSH_TIME);
        } else {
            if ($egerep_request_count == self::REQUEST_MAX_COUNT) {
                Sms::sendToAdmins('Внимание! DDoS на заявки');
            }
            Redis::sadd(self::REDIS_REQUEST_BLOCKED, json_encode($request->input()));
            return abort(403);
        }
    }
}
