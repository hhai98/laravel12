<?php

namespace App\Http\Requests;

class NewsRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'created_by' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
