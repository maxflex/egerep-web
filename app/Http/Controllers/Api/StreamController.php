<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use DB;

class StreamController extends Controller
{
    public function store(Request $request)
    {
        // добавляем referer
        if ($request->action == 'landing') {
            $request->merge(['referer' => $_SERVER['HTTP_REFERER']]);
        }
        $request->merge(['mobile' => isMobile()]);
        egerep('stream')->insert($request->all());
    }
}
