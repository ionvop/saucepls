<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sauce_answer_id',
    'user_id',
])]
class SauceAnswerLike extends Model
{
    public function answer(): BelongsTo
    {
        return $this->belongsTo(SauceAnswer::class, 'sauce_answer_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
