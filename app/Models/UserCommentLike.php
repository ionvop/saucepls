<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_comment_id',
    'user_id',
])]
class UserCommentLike extends Model
{
    public function comment(): BelongsTo
    {
        return $this->belongsTo(UserComment::class, 'user_comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}