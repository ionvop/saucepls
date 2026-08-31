<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'sauce_request_id',
    'user_id',
    'parent_id',
    'content',
])]
class SauceRequestComment extends Model
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
     * The sauce request this comment belongs to.
     */
    public function sauceRequest(): BelongsTo
    {
        return $this->belongsTo(SauceRequest::class);
    }

    /**
     * The user who wrote the comment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The top-level comment this comment is a reply to, or null when it is
     * itself a top-level comment.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * The replies to this comment. Replies are limited to one level deep,
     * so these are always top-level comments' direct children.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The likes this comment has received.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(SauceRequestCommentLike::class);
    }
}