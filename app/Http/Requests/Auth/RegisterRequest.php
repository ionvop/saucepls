<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users', 'username'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'The username may only contain letters, numbers, hyphens, and underscores.',
        ];
    }
}
