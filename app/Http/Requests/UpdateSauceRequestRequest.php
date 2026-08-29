<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSauceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sauceRequest = $this->route('sauceRequest');

        // Only the owner of the request may edit it.
        return $sauceRequest !== null
            && $this->user()?->id === $sauceRequest->user_id;
    }

    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'is_explicit' => ['sometimes', 'boolean'],
            'tags' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
