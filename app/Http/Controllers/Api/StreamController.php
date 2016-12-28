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
        egerep('stream')->insert($request->all());
    }
}
