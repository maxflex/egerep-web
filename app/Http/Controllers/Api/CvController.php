<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Service\Api;

class CvController extends Controller
{
    public function store(Request $request)
    {
        Api::exec('tutorNew', $request->input());
    }

    public function uploadPhoto(Request $request)
    {

    }
}
