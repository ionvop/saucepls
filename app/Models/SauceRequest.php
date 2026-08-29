<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'user_id',
    'title',
    'description',
    'text',
    'image_path',
    'accepted_sauce',
    'phash64',
    'is_explicit',
    'published_at',
])]
class SauceRequest extends Model
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
            'is_explicit' => 'boolean',
            'accepted_sauce' => 'integer',
            'deleted_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    /**
     * The user who posted the sauce request.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope the query to only published sauce requests.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at');
    }

    /**
     * The tags attached to the sauce request.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'sauce_request_tags')
            ->withTimestamps();
    }

    /**
     * The tagging changes recorded against this sauce request, newest first.
     */
    public function taggingHistory(): HasMany
    {
        return $this->hasMany(SauceRequestTaggingHistory::class)
            ->latest('id');
    }

    /**
     * The absolute URL to the request's image.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->image_path) {
                return null;
            }

            return Storage::disk('public')->url($this->image_path);
        });
    }

    /**
     * Whether the request has an accepted sauce answer.
     */
    public function isAccepted(): bool
    {
        return $this->accepted_sauce !== null;
    }
}
