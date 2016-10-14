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
        if ($request->hasFile('photo')) {
            $filename = uniqid() . '.' . $request->photo->extension();
            $request->photo->storeAs('tutors', $filename);
            return $filename;
        } else {
            abort(400);
        }
    }
}
