<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSauceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['required', 'image', 'mimes:jpeg,png,webp,gif', 'max:10240'],
            'is_explicit' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Please choose an image to request the sauce for.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'The image must be a JPEG, PNG, WebP, or GIF file.',
            'image.max' => 'The image may not be larger than 10MB.',
        ];
    }
}