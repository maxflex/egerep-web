<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Service\Api;
use App\Models\Service\Sms;
use Illuminate\Support\Facades\Redis;

class CvController extends Controller
{
    const REDIS_CV_COUNT     = 'egerep:cv:count';
    const REDIS_CV_BLOCKED   = 'egerep:cv:blocked';
    const MAX_COMMENT_LENGTH = 1000;
    const MAX_NAME_LENGTH    = 60;
    const CV_MAX_COUNT       = 200;
    const CV_FLUSH_TIME      = 3600 * 24;

    public function store(Request $request)
    {
        $rules = [
            'phone'            => ['Required', 'Size:18', 'Regex:' . PHONE_REGEX],
            'birth_year'       => 'digits           :4',
            'experience_years' => 'max              :2'
        ];
        foreach(['first_name', 'last_name', 'middle_name'] as $field) {
            $rules[$field] = [
                'regex:' . NAME_VALIDATION_REGEX,
                'max:' . self::MAX_NAME_LENGTH,
            ];
        }
        foreach(['education', 'achievements', 'tutoring_experience', 'students_category', 'preferences', 'schedule', 'price', 'contacts'] as $field) {
            $rules[$field] = [
                'regex:' . TEXT_VALIDATION_REGEX,
                'max:' . self::MAX_COMMENT_LENGTH,
            ];
        }

        $this->validate($request, $rules);

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
            return $filename;
        } else {
            abort(400);
        }
    }
}
