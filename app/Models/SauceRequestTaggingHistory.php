<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sauce_request_id', 'user_id', 'added_tags', 'removed_tags'])]
class SauceRequestTaggingHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sauce_request_tagging_history';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'added_tags' => 'array',
            'removed_tags' => 'array',
        ];
    }

    /**
     * The sauce request that was tagged.
     */
    public function sauceRequest(): BelongsTo
    {
        return $this->belongsTo(SauceRequest::class);
    }

    /**
     * The user who made the tagging change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
