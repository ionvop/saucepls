<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sauce_answer_id',
    'user_id',
    'content',
])]
class SauceAnswerComment extends Model
{
    use SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * The sauce answer this comment belongs to.
     */
    public function sauceAnswer(): BelongsTo
    {
        return $this->belongsTo(SauceAnswer::class);
    }

    /**
     * The user who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The likes this comment has received.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(SauceAnswerCommentLike::class);
    }
}