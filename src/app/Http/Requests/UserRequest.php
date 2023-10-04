<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        // Получите все данные из запроса и преобразуйте их в нижний регистр
        $data = $this->all();

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = strtolower($value);
            }
        }

        $this->replace($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'field' => 'nullable|in:name,surname,email,phone,country',
            'orderBy' => 'nullable|in:asc,desc',
            'type' => 'nullable|in:json,xml',
            'limit' => 'integer|min:1',
            'page' => 'integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'field.in' => 'The field must be one of: name, surname, email, phone, country.',
            'orderBy.in' => 'The orderBy must be one of: asc, desc.',
            'type.in' => 'The type must be one of: json, xml.',
            'limit.integer' => 'The limit must be an integer.',
            'limit.min' => 'The limit cannot be less than 1.',
            'page.integer' => 'The page must be an integer.',
            'page.min' => 'The page cannot be less than 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
