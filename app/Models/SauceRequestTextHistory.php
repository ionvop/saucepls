<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sauce_request_id', 'user_id', 'text_snapshot'])]
class SauceRequestTextHistory extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sauce_request_text_history';

    /**
     * The sauce request whose extracted text was changed.
     */
    public function sauceRequest(): BelongsTo
    {
        return $this->belongsTo(SauceRequest::class);
    }

    /**
     * The user who made the text change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}