<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Service\Api;
use App\Http\Requests\RequestStore;
use App\Service\Limiter;
use DB;

class RequestsController extends Controller
{
    public function store(RequestStore $request)
    {
        DB::table('request_log')->insert($request->all());
        return Limiter::run('request', 24, 200, function() use ($request) {
            $_SESSION['sent_ids'][] = $request->tutor_id;
            Api::exec('requestNew', $request->input());
        }, function() use ($request) {
            Redis::sadd('egerep:request:blocked', json_encode($request->input()));
        }, 'Внимание! DDoS на заявки');
    }
}
