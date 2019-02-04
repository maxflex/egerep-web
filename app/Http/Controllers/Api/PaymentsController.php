<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests\PaymentProceed;
use App\Http\Controllers\Controller;

class PaymentsController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => 'https://web.rbsuat.com/ab/rest/',]);
    }

    public function proceed(PaymentProceed $request)
    {
        $response = $this->client->request('POST', 'register.do', [
            'form_params' => [
                'orderNumber' => uniqid(),
                'userName' => config('payment.login'),
                'password' => config('payment.password'),
                'returnUrl' => config('app.url') . 'payment',
                'amount' => $request->sum * 100,
                'description' => $request->fio,
            ]
        ]);

        return $response->getBody();
    }

    public function getOrderStatus(Request $request)
    {
        $response = $this->client->request('POST', 'getOrderStatus.do', [
            'form_params' => [
                'userName' => config('payment.login'),
                'password' => config('payment.password'),
                'orderId' => $request->orderId,
                'language' => $request->lang,
            ],
        ]);

        return $response->getBody();
    }
}
