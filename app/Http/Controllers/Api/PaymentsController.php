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
                'email' => $request->email,
            ]
        ]);
        
        return $response->getBody();
    }

    public function getOrderStatus(Request $request)
    {
        $response = $this->client->request('POST', 'getOrderStatusExtended.do', [
            'form_params' => [
                'userName' => config('payment.login'),
                'password' => config('payment.password'),
                'orderId' => $request->orderId,
                'language' => $request->lang,
            ],
        ]);
        
        $order = json_decode($response->getBody()->getContents());
            
        $this->registerOrder($request->orderId, $order);
        
        return response()->json($order);
    }

    /**
     * Зарегистрировать на кассе
     */
    private function registerOrder($orderId, $order)
    {
        $price = $order->amount / 100;
        $client = new \GuzzleHttp\Client(['base_uri' => config('payment.terminal-host')]);
        $response = $client->request('POST', 'stores/20190131-3AD9-40A2-806B-B23697976775/sales/add', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Authorization' => config('payment.terminal-password')
            ],
            'json' => [
                [
                    'uuid' => uniqid(),
                    'doc_date' => now(),
                    'doc_num' => $order->orderNumber,
                    'client_uuid' => uniqid(),
                    'client_name' => $order->orderDescription,
                    'dsum' => $price,
                    'debt' => $price,
                    'info' => '',
                    'emailphone' => $order->payerData->email,
                    'pay_type' => 1,
                    'firm_address' => 'ege-repetitor.ru',
                    'goods' => [
                        [
                            'good_uuid' => 1,
                            'good_name' => 'Услуга по распространению рекламно-информационных материалов',
                            'good_code' => 1,
                            'quantity' => 1.000,
                            'price' => $price,
                            'dsum' => $price,
                            'vat_rate' => 0,
                            'vat_sum' => 0.00,
                            'unit_uuid' => 1,
                            'unit_name' => 'услуга',
                            'tag1214' => 4,
                        ]
                    ],
                ]
            ],
        ]);
    }
}
