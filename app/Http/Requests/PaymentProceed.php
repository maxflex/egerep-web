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
            'sum'   => ['required', 'numeric', 'min:1'],
            'fio'   => ['required']
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
            'min' => 'Минимальная сума оплаты – 1 руб.',
            'numeric' => 'Сума должна быть введена числом',
            'sum.required' => 'Введите сумму для оплаты',
            'fio.required' => 'Введите ваше ФИО',
        ];
    }
}
