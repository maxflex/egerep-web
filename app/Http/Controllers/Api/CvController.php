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
    public function store(CvStore $request)
    {
        // не более 200 за последние 24 часа
        return Limiter::run('cv', 24, 200, function() use ($request) {
            Api::exec('tutorNew', $request->input());
        }, function() use ($request) {
            Redis::sadd('egerep:cv:blocked', json_encode($request->input()));
        }, 'Внимание! DDoS на анкеты');
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
