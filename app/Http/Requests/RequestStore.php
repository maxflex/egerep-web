<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RequestStore extends FormRequest
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
            'phone'   => ['required', 'phone_filled', 'size:18', 'regex:' . PHONE_REGEX],
            'name'    => [
                'regex:' . TEXT_VALIDATION_REGEX,
                'max:' . MAX_NAME_LENGTH,
            ],
            'comment' => [
                'regex:' . TEXT_VALIDATION_REGEX,
                'max:' . MAX_COMMENT_LENGTH,
            ]
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
            'name.regex'    => trans('validation.name_regex'),
            'comment.regex' => trans('validation.comment_regex'),
        ];
    }
}
