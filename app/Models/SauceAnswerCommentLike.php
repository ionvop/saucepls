<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sauce_answer_comment_id',
    'user_id',
])]
class SauceAnswerCommentLike extends Model
{
    public function comment(): BelongsTo
    {
        return $this->belongsTo(SauceAnswerComment::class, 'sauce_answer_comment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}