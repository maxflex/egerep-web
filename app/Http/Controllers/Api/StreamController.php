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
        $ip = @$_SERVER['HTTP_X_REAL_IP'];

        if (strpos($ip, '213.184.130.') === 0 || $ip == '77.37.220.250' || isTestSubdomain() || isDevSubdomain()) {
            return;
        }

        // добавляем referer
        if ($request->action == 'landing') {
            $request->merge(['referer' => $_SERVER['HTTP_REFERER']]);
        }

        $request->merge([
            'age_from'  => $request->age_from ? filter_var($request->age_from, FILTER_SANITIZE_NUMBER_INT) : null,
            'age_to'    => $request->age_to ? filter_var($request->age_to, FILTER_SANITIZE_NUMBER_INT) : null,
            'gender'    => isBlank(@$request->gender) ? null : ($request->gender == 'male' ? 1 : 0),
            'mobile'    => isMobile(),
            'ip'        => $ip,
        ]);

        egerep('stream')->insert($request->all());
    }
}
