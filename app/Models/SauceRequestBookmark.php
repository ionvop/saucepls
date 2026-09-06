<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sauce_request_id',
    'user_id',
])]
class SauceRequestBookmark extends Model
{
    public function request(): BelongsTo
    {
        return $this->belongsTo(SauceRequest::class, 'sauce_request_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}