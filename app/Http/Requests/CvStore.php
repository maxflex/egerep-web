<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CvStore extends FormRequest
{
    const NAME_FIELDS = ['first_name', 'last_name', 'middle_name'];
    const TEXT_FIELDS = ['education', 'achievements', 'tutoring_experience', 'students_category', 'preferences', 'schedule', 'price', 'contacts'];

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
        $rules = [
            'phone'            => ['required', 'phone_filled', 'size:18', 'regex:' . PHONE_REGEX],
            'birth_year'       => 'digits:4',
            'experience_years' => 'digits_between:1,2',
        ];
        foreach(self::NAME_FIELDS as $field) {
            $rules[$field] = [
                'regex:' . NAME_VALIDATION_REGEX,
                'max:' . MAX_NAME_LENGTH,
            ];
        }
        foreach(self::TEXT_FIELDS as $field) {
            $rules[$field] = [
                'regex:' . TEXT_VALIDATION_REGEX,
                'max:' . MAX_COMMENT_LENGTH,
            ];
        }
        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        $messages = [];
        foreach(self::NAME_FIELDS as $field) {
            $messages[$field . '.regex'] = trans('validation.name_regex');
        }
        foreach(self::TEXT_FIELDS as $field) {
            $messages[$field . '.regex'] = trans('validation.comment_regex');
        }
        return $messages;
    }
}
