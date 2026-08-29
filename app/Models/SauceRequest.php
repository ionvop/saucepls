<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
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
     * The absolute URL to the request's image.
     */
    protected function imageUrl(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::get(function (): ?string {
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