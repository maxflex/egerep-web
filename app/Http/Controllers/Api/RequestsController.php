<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use Cache;
use App\Http\Controllers\Controller;
use App\Models\Service\Api;
use Illuminate\Support\Facades\Redis;

class RequestsController extends Controller
{
    const REDIS_REQUEST_COUNT = 'egerep:request:count';
    const REDIS_REQUEST_BLOCKED = 'egerep:request:blocked';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // if (! session()->exists('sent_tutor_ids')) {
        //     session('sent_tutor_ids', []);
        // }
        // session()->push('sent_tutor_ids', $request->tutor_id);
        // $sent_tutor_ids = $_SESSION['sent_tutor_ids'] || [];
        // $sent_tutor_ids[] = $request->tutor_id;
        $egerep_request_count = intval(Redis::get(self::REDIS_REQUEST_COUNT));

        // максимум – 10 заявок в минуту
        if ($egerep_request_count < 10) {
            $_SESSION['sent_ids'][] = $request->tutor_id;
            Api::exec('requestNew', $request->input());
            Redis::incr(self::REDIS_REQUEST_COUNT);
            Redis::expire(self::REDIS_REQUEST_COUNT, 60);
        } else {
            Redis::sadd(self::REDIS_REQUEST_BLOCKED, json_encode($request->input()));
            return abort(403);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
