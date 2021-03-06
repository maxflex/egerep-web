<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentProceed extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'sum'   => [
                'required', 
                'numeric', 
                'min:10',
                'dividedby',
            ],
            'fio'   => ['required'],
            'email' => ['required', 'email'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'min' => 'Минимальная сумма оплаты – 10 руб.',
            'numeric' => 'Сумма должна быть введена числом',
            'dividedby' => 'Введите сумму, кратную 10 руб.',
            'sum.required' => 'Введите сумму для оплаты',
            'fio.required' => 'Введите ваше ФИО',
            'email.required' => 'Укажите email, на него будет отправлен чек',
            'email.email' => 'Email указан неверно',
        ];
    }
}
