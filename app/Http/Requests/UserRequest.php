<?php

namespace App\Http\Requests;

class UserRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'phone' => ['required', 'string', 'max:255'],
            'sns_id' => ['nullable', 'string', 'max:255'],
            'sns_driver' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
        ];

        // Add unique rule for phone, excluding current record on update
        if ($this->isCreate()) {
            $rules['phone'][] = 'unique:users,phone';
        } else {
            $rules['phone'][] = 'unique:users,phone,' . $this->getRouteId();
        }

        return $rules;
    }
}
