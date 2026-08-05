<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        $rules = [
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,webp,gif', 'max:2048'],
            'description' => ['nullable', 'string', 'max:5000'],
        ];

        // Only allow a username change when the cooldown has elapsed.
        if ($user instanceof User && $user->canChangeUsername()) {
            $rules['username'] = [
                'nullable',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-zA-Z0-9_-]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'username.regex' => 'The username may only contain letters, numbers, hyphens, and underscores.',
            'avatar.image' => 'The avatar must be an image.',
            'avatar.max' => 'The avatar may not be larger than 2MB.',
        ];
    }
}
