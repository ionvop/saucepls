<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'description'])]
class Tag extends Model
{
    /**
     * The sauce requests that carry this tag.
     */
    public function sauceRequests(): BelongsToMany
    {
        return $this->belongsToMany(SauceRequest::class, 'sauce_request_tags')
            ->withTimestamps();
    }
}
