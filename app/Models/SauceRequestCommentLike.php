<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sauce_request_comment_id',
    'user_id',
])]
class SauceRequestCommentLike extends Model
{
    public function comment(): BelongsTo
    {
        return $this->belongsTo(SauceRequestComment::class, 'sauce_request_comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}