<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Service\Api;
use App\Http\Requests\RequestStore;
use App\Service\Limiter;
use Illuminate\Support\Facades\Redis;
use DB;
use ReCaptcha\ReCaptcha;

class RequestsController extends Controller
{
    public function store(RequestStore $request)
    {
        /**
         * Проверка капчи
         */
        if (! isMobile()) {
            $recaptcha = new ReCaptcha(config('captcha.secret'));
            $resp = $recaptcha->verify($request->captcha, @$_SERVER['HTTP_X_REAL_IP']);
        }

        if (isMobile() || $resp->isSuccess()) {
            DB::table('request_log')->insert($request->except('captcha'));
            $_SESSION['sent_ids'][] = $request->tutor_id;
            Api::exec('requestNew', $request->input());
        } else {
            return abort(403);
        }
    }
}
