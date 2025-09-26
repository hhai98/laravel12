<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

abstract class BaseFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Override in child classes if needed
    }

    /**
     * Get the validation rules that apply to the request.
     */
    abstract public function rules(): array;

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute field must be a string.',
            'email' => 'The :attribute field must be a valid email address.',
            'unique' => 'The :attribute has already been taken.',
            'exists' => 'The selected :attribute is invalid.',
            'min' => 'The :attribute field must be at least :min characters.',
            'max' => 'The :attribute field must not be greater than :max characters.',
            'numeric' => 'The :attribute field must be a number.',
            'integer' => 'The :attribute field must be an integer.',
            'boolean' => 'The :attribute field must be true or false.',
            'array' => 'The :attribute field must be an array.',
            'file' => 'The :attribute field must be a file.',
            'image' => 'The :attribute field must be an image.',
            'mimes' => 'The :attribute field must be a file of type: :values.',
            'confirmed' => 'The :attribute confirmation does not match.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'name',
            'email' => 'email',
            'password' => 'password',
            'password_confirmation' => 'password confirmation',
            'phone' => 'phone number',
            'address' => 'address',
            'title' => 'title',
            'description' => 'description',
            'content' => 'content',
            'status' => 'status',
            'role_id' => 'role',
            'user_id' => 'user',
            'created_by' => 'creator',
            'is_show' => 'visibility',
            'rate' => 'rating',
            'author_name' => 'author name',
            'code' => 'code',
            'type' => 'type',
            'url' => 'URL',
            'thumb' => 'thumbnail',
            'geo' => 'coordinates',
            'sns_id' => 'social network ID',
            'sns_driver' => 'social network type',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $validator->errors(),
            ], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim all string inputs
        $input = $this->all();
        
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);
                // Convert empty strings to null
                if ($value === '') {
                    $value = null;
                }
            }
        });
        
        $this->replace($input);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        // Add custom validation logic here if needed
    }

    /**
     * Get validated data with only fillable attributes.
     */
    public function validatedFillable(array $fillable = []): array
    {
        $validated = $this->validated();
        
        if (empty($fillable)) {
            return $validated;
        }
        
        return array_intersect_key($validated, array_flip($fillable));
    }

    /**
     * Get data for creating/updating relationships.
     */
    public function getRelationshipData(string $key): array
    {
        return $this->input($key, []);
    }

    /**
     * Check if request is for update operation.
     */
    public function isUpdate(): bool
    {
        return $this->isMethod('PUT') || $this->isMethod('PATCH');
    }

    /**
     * Check if request is for create operation.
     */
    public function isCreate(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * Get the route parameter (usually ID).
     */
    public function getRouteId(): mixed
    {
        return $this->route()->parameter('id') ?? 
               $this->route()->parameter($this->getModelRouteKey());
    }

    /**
     * Get the model route key name.
     * Override this in child classes if using different route parameter names.
     */
    protected function getModelRouteKey(): string
    {
        return 'id';
    }

    /**
     * Add conditional validation rules.
     */
    protected function addConditionalRules(array &$rules, string $condition, array $conditionalRules): void
    {
        if ($this->has($condition)) {
            $rules = array_merge($rules, $conditionalRules);
        }
    }

    /**
     * Add rules for update operation only.
     */
    protected function addUpdateRules(array &$rules, array $updateRules): void
    {
        if ($this->isUpdate()) {
            $rules = array_merge($rules, $updateRules);
        }
    }

    /**
     * Add rules for create operation only.
     */
    protected function addCreateRules(array &$rules, array $createRules): void
    {
        if ($this->isCreate()) {
            $rules = array_merge($rules, $createRules);
        }
    }
}
