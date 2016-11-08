<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\SmsCode;
use App\Models\Tutor;
use App\Models\Service\Sms;

class SmsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $code = SmsCode::get($_SESSION['tutor_id'], $request->code);
        if ($code !== null) {
            $code->activate();
        } else {
            abort(422);
        }
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
        $tutor = Tutor::findByPhone($request->phone)->where('debt_calc', '>', 0)->where('debtor', 0);
        if ($tutor->exists()) {
            $tutor_id = $tutor->value('id');
            $code = SmsCode::create(compact('tutor_id'))->code;
            $_SESSION['tutor_id'] = $tutor_id;
            Sms::send($request->phone, $code . ' – код доступа к личному кабинету ЕГЭ-Репетитор');
        } else {
            abort(422);
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
