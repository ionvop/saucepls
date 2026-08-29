<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sauce_request_id', 'tag_id'])]
class SauceRequestTag extends Model
{
    /**
     * The sauce request this pivot row belongs to.
     */
    public function sauceRequest(): BelongsTo
    {
        return $this->belongsTo(SauceRequest::class);
    }

    /**
     * The tag this pivot row belongs to.
     */
    public function tag(): BelongsTo
    {
        return $this->belongsTo(Tag::class);
    }
}
