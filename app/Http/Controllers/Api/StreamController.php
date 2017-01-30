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
        // если первая запись, добавляем referer и referer_url
        if ($request->action == 'landing' && ! egerep('stream')->where('google_id', $request->google_id)->exists()) {
            $request->merge(['referer' => $_SERVER['HTTP_REFERER']]);
        }
        $request->merge(['mobile' => isMobile()]);
        egerep('stream')->insert($request->all());
    }
}
