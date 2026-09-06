<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
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

    /**
     * Scope tags to autocomplete suggestions for a search prefix.
     *
     * Returns tags whose name starts with the given prefix, ordered first by
     * how many *published, non-deleted* sauce requests they are used on
     * (descending) and then alphabetically. Only requests that would actually
     * appear in the search feed count toward the usage tally, so soft-deleted
     * and still-draft requests are excluded. Counts are aggregated from the
     * `sauce_request_tags` pivot at query time.
     */
    public function scopeAutocomplete(Builder $query, string $term): Builder
    {
        return $query
            ->select('tags.id', 'tags.name')
            ->selectRaw('COUNT(sauce_request_tags.tag_id) AS usage_count')
            ->leftJoin('sauce_request_tags', 'sauce_request_tags.tag_id', '=', 'tags.id')
            ->leftJoin('sauce_requests', 'sauce_requests.id', '=', 'sauce_request_tags.sauce_request_id')
            ->whereNull('sauce_requests.deleted_at')
            ->whereNotNull('sauce_requests.published_at')
            ->whereLike('tags.name', $term.'%')
            ->groupBy('tags.id', 'tags.name')
            ->orderByDesc('usage_count')
            ->orderBy('tags.name');
    }
}
