<?php

namespace App\Http\Requests;

class RoleRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'code' => ['required', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
        ];

        // Add unique rule for code, excluding current record on update
        if ($this->isCreate()) {
            $rules['code'][] = 'unique:roles,code';
        } else {
            $rules['code'][] = 'unique:roles,code,' . $this->getRouteId();
        }

        return $rules;
    }
}
