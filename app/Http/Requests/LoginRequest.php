<?php

namespace App\Http\Requests;

class LoginRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'max:255',
            ],
            'password' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return array_merge(parent::messages(), [
            'phone.required' => 'Please enter your phone number.',
            'password.required' => 'Please enter your password.',
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return array_merge(parent::attributes(), [
            'phone' => 'phone number',
            'password' => 'password',
        ]);
    }
}
