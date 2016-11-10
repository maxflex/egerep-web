<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\SmsCode;
use App\Models\Tutor;
use App\Models\Service\Sms;
use App\Http\Requests\SmsStore;
use App\Http\Requests\SmsIndex;
use App\Service\Limiter;
use Illuminate\Support\Facades\Redis;

class SmsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SmsIndex $request)
    {
        $tutor_id = $_SESSION['tmp_tutor_id'];
        if (cache("codes:{$tutor_id}") == $request->code) {
            Tutor::login($tutor_id);
        } else {
            $_SESSION['incorrect_code'] = isset($_SESSION['incorrect_code']) ? ($_SESSION['incorrect_code'] + 1) : 0;
            // если код введен неправильно 3 раза – выкидываем
            if ($_SESSION['incorrect_code'] > 3) {
                unset($_SESSION['incorrect_code']);
                unset($_SESSION['tmp_tutor_id']);
                return abort(403);
            }
            return abort(422);
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
    public function store(SmsStore $request)
    {
        return Limiter::run('sms', 24, 20, function() use ($request) {
            $tutor = Tutor::loggable()->findByPhone($request->phone);
            if ($tutor->exists()) {
                $tutor_id = $tutor->value('id');
                $code = Sms::generateCode($tutor_id);
                $phone = cleanNumber($request->phone);
                $_SESSION['tmp_tutor_id'] = $tutor_id;
                $data = compact('code', 'phone');
                return Limiter::run("code:{$phone}", 24, 5, function() use ($data) {
                    Sms::send($data['phone'], $data['code'] . ' – код доступа к личному кабинету ЕГЭ-Репетитор');
                });
            } else {
                return abort(422);
            }
        });
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
