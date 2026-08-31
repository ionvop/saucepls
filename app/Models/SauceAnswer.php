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
    'content',
    'url',
])]
class SauceAnswer extends Model
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
     * The sauce request this answer belongs to.
     */
    public function sauceRequest(): BelongsTo
    {
        return $this->belongsTo(SauceRequest::class);
    }

    /**
     * The user who provided the answer.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The likes this answer has received.
     */
    public function likes(): HasMany
    {
        return $this->hasMany(SauceAnswerLike::class);
    }
}
