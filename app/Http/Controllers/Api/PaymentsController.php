<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

use App\Http\Requests\PaymentProceed;
use App\Http\Controllers\Controller;
use App\Models\Variable;

class PaymentsController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => config('payment.host')]);
    }

    public function index(Request $request)
    {

        // Зарегистрировать на кассе
        // if (isset($request->orderId)) {
        //     $this->registerOrder($request->orderId);
        // }
        $html = Variable::display('page-payment');
        return $html;
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

    // private function registerOrder($orderId)
    // {
    //     $client = new \GuzzleHttp\Client(['base_uri' => config('payment.terminal-host')]);
    //     $response = $client->request('POST', 'stores/20190131-3AD9-40A2-806B-B23697976775/sales/add', [
    //         'headers' => [
    //             'Content-Type' => 'application/json',
    //             'X-Authorization' =>  config('payment.terminal-password')
    //         ],
    //         'json' => [
    //             [
    //                 'uuid' => 
    //             ]
    //         ],
    //     ]);
    // }

    // "uuid": "0ecab77f-7062-4a5f-aa20-35213db1397c",
    // "doc_date": "2016-08-25 13:48:01",
    // "doc_num": "ТД00-000001",
    // "client_uuid": "4a65ecb6-8b1b-11df-be16-e0cb4ed5f70f",
    // "client_name": "Kikinda (Сербия)",
    // "dsum": 80.25,
    // "debt": 80.25,
    // "info": "",
    // "emailphone": "79111111111",
    // "pay_type": 1,
    // "firm_address": "мой.магазин.рф",
    // "goods": [
    //   {
    //     "good_uuid": "dee6e1a8-55bc-11d9-848a-00112f43529a",
    //     "good_name": "Кондиционер БК-2300",
    //     "good_code": "00001",
    //     "quantity": 1.000,
    //     "price": 80.25,
    //     "dsum": 80.25,
    //     "vat_rate": 0,
    //     "vat_sum": 0.00,
    //     "unit_uuid": "bd72d926-55bc-11d9-848a-00112f43529a",,
    //     "unit_name": "шт",
    //     "tag1214": 4
    //   }
    // ]

}
