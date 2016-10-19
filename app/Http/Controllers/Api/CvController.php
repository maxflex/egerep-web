<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\CvStore;
use App\Http\Controllers\Controller;
use App\Models\Service\Api;
use App\Models\Service\Sms;
use Illuminate\Support\Facades\Redis;

class CvController extends Controller
{
    const REDIS_CV_COUNT     = 'egerep:cv:count';
    const REDIS_CV_BLOCKED   = 'egerep:cv:blocked';
    const CV_MAX_COUNT       = 200;
    const CV_FLUSH_TIME      = 3600 * 24;

    public function store(CvStore $request)
    {
        $egerep_cv_count = intval(Redis::get(self::REDIS_CV_COUNT));
        // не более 100 за последние 24 часа
        if ($egerep_cv_count < self::CV_MAX_COUNT) {
            Api::exec('tutorNew', $request->input());
            Redis::incr(self::REDIS_CV_COUNT);
            Redis::expire(self::REDIS_CV_COUNT, self::CV_FLUSH_TIME);
        } else {
            if ($egerep_cv_count == self::CV_MAX_COUNT) {
                Sms::sendToAdmins('Внимание! DDoS на анкеты');
            }
            Redis::sadd(self::REDIS_CV_BLOCKED, json_encode($request->input()));
            return abort(403);
        }
    }

    public function uploadPhoto(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'photo' => 'image|max:10000'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 401);
        }

        if ($request->hasFile('photo')) {
            $filename = uniqid() . '.' . $request->photo->extension();
            $request->photo->storeAs('uploads', $filename);
            return [
                'filename' => $filename,
                'size' => getSize($request->photo)
            ];
        } else {
            abort(400);
        }
    }
}
