<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

#[Fillable(['username', 'email', 'description', 'type', 'avatar_path', 'username_changed_at', 'last_seen_at', 'banned_until', 'hide_nsfw'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    /**
     * How long a user must wait before changing their username again.
     */
    public const USERNAME_CHANGE_COOLDOWN_MINUTES = 5;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_seen_at' => 'datetime',
            'username_changed_at' => 'datetime',
            'banned_until' => 'datetime',
            'deleted_at' => 'datetime',
            'hide_nsfw' => 'boolean',
        ];
    }

    /**
     * The absolute URL to the user's avatar, or null when none is set.
     */
    protected function avatarUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            if (! $this->avatar_path) {
                return null;
            }

            return Storage::disk('public')->url($this->avatar_path);
        });
    }

    /**
     * Whether the user is allowed to change their username right now.
     */
    public function canChangeUsername(): bool
    {
        return $this->usernameChangeAvailableAt()->isPast();
    }

    /**
     * The timestamp at which the username change cooldown expires.
     */
    public function usernameChangeAvailableAt(): \Illuminate\Support\Carbon
    {
        $changedAt = $this->username_changed_at;

        if (! $changedAt) {
            return now()->subSecond();
        }

        return $changedAt->copy()->addMinutes(self::USERNAME_CHANGE_COOLDOWN_MINUTES);
    }

    /**
     * The sauce requests posted by the user.
     */
    public function sauceRequests(): HasMany
    {
        return $this->hasMany(SauceRequest::class);
    }

    /**
     * The comments written by the user.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(SauceRequestComment::class);
    }

    /**
     * Whether the user is a moderator or admin (i.e. not a regular member).
     */
    public function isStaff(): bool
    {
        return $this->type !== 'member';
    }
}
