<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublishSauceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->route('sauceRequest')?->user_id === $this->user()?->id;
    }

    public function rules(): array
    {
        return [
            'text' => ['nullable', 'string', 'max:5000'],
            'tags' => ['nullable', 'string', 'max:1000'],
        ];
    }
}