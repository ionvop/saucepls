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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Carbon\CarbonInterval;

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
     * Scope the query to only solved sauce requests (those with an
     * accepted answer).
     */
    public function scopeSolved(Builder $query): Builder
    {
        return $query->whereNotNull('accepted_sauce');
    }

    /**
     * Scope the query to only unsolved sauce requests (those without an
     * accepted answer).
     */
    public function scopeUnsolved(Builder $query): Builder
    {
        return $query->whereNull('accepted_sauce');
    }

    /**
     * Scope the query by a search string.
     *
     * The search entry is parsed word by word and only sauce requests that
     * contain all of the words are returned. Each word can match the title,
     * description, extracted text, or a tag name.
     *
     * Supports the search syntax from docs/proposal.md:
     *  - Quoted phrases: "coconut doggy" matches the exact phrase.
     *  - Typed prefixes: tag:1girl text:"coconut doggy" narrow a word to a
     *    single field.
     *  - Date prefixes: since:2026-04-20 until:2026-09-11 within:5d filter by
     *    the published date. within accepts durations in hours (h), days (d),
     *    weeks (w), months (m), or years (y). since/until dates are interpreted
     *    in the given timezone and converted to UTC to align with the stored
     *    naive UTC datetimes.
     *  - Exclusions: a leading hyphen (e.g. -kitty) excludes requests that
     *    contain the word anywhere.
     *
     * @param  string|null  $search
     * @param  string|null  $timezone  IANA timezone for date prefixes (defaults to UTC).
     */
    public function scopeSearch(Builder $query, ?string $search, ?string $timezone = null): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        foreach ($this->tokenizeSearch($search) as $word) {
            $query->where(function (Builder $sub) use ($word, $timezone) {
                $this->applyWordMatches($sub, $word, $timezone);
            });
        }

        return $query;
    }

    /**
     * Scope the query to order by most bookmarks first.
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query
            ->orderByDesc('bookmarks_count')
            ->orderByDesc('id');
    }

    /**
     * Scope the query to order by the number of bookmarks received within
     * the past week, most first.
     */
    public function scopeTrending(Builder $query): Builder
    {
        $query->withCount([
            'bookmarks as trending_bookmarks_count' => fn (Builder $bookmarks) => $bookmarks
                ->where('created_at', '>=', now()->subWeek()),
        ]);

        return $query
            ->orderByDesc('trending_bookmarks_count')
            ->orderByDesc('id');
    }

    /**
     * Tokenize a raw search string into word groups.
     *
     * @return array<int, array{field: string|null, exclude: bool, term: string}>
     */
    private function tokenizeSearch(string $search): array
    {
        $words = [];

        // Match, in order of precedence:
        //   1. A typed prefix followed by a quoted phrase, e.g. tag:"coconut doggy"
        //      or -text:"coconut doggy" (the prefix may carry an exclusion hyphen).
        //   2. A bare quoted phrase, e.g. "coconut doggy".
        //   3. Any other whitespace-delimited token, e.g. kitty, -kitty, tag:kitty.
        if (preg_match_all('/(-?(?:tag|text|since|until|within):"[^"]*")|("[^"]*")|(\S+)/', $search, $matches, PREG_SET_ORDER) === false) {
            return $words;
        }

        foreach ($matches as $match) {
            $exclude = false;
            $field = null;

            if ($match[1] !== '') {
                // Typed prefix followed by a quoted phrase: tag:"coconut doggy".
                $field = strtolower(ltrim($match[1], '-'));
                $exclude = str_starts_with($match[1], '-');
                $raw = preg_replace('/^.*?:"([^"]*)"$/', '$1', $match[1]) ?? '';
            } else {
                $raw = $match[2] !== '' ? $match[2] : $match[3];

                if ($raw === '') {
                    continue;
                }

                $exclude = str_starts_with($raw, '-');

                // Strip a leading hyphen (the exclusion marker) before matching
                // typed prefixes, so `-tag:kitty` and `-text:"kitty"` parse as
                // scoped exclusions rather than general exclusions for the
                // literal `tag:kitty` / `text:"kitty"` strings.
                if ($exclude) {
                    $raw = ltrim($raw, '-');
                }

                if (preg_match('/^(tag|text|since|until|within):"([^"]*)"$/i', $raw, $typed) === 1) {
                    $field = strtolower($typed[1]);
                    $raw = $typed[2];
                } elseif (preg_match('/^(tag|text|since|until|within):(\S+)$/i', $raw, $typed) === 1) {
                    $field = strtolower($typed[1]);
                    $raw = $typed[2];
                }
            }

            if ($raw === '') {
                continue;
            }

            $words[] = [
                'field' => $field,
                'exclude' => $exclude,
                'term' => $raw,
            ];
        }

        return $words;
    }

    /**
     * Apply a single search word's filters inside a shared where group.
     *
     * @param  array{field: string|null, exclude: bool, term: string}  $word
     * @param  string|null  $timezone  IANA timezone for date prefixes.
     */
    private function applyWordMatches(Builder $query, array $word, ?string $timezone = null): void
    {
        $term = $this->normalizeSearchTerm($word['term']);

        if ($word['field'] === 'tag') {
            $this->applyTagMatch($query, $term, $word['exclude']);

            return;
        }

        if ($word['field'] === 'text') {
            $this->applyTextMatch($query, $term, $word['exclude']);

            return;
        }

        if (in_array($word['field'], ['since', 'until', 'within'], true)) {
            $this->applyDateMatch($query, $word['field'], $term, $timezone);

            return;
        }

        $this->applyGeneralMatch($query, $term, $word['exclude']);
    }

    /**
     * Match a search term against the request's tags.
     */
    private function applyTagMatch(Builder $query, string $term, bool $exclude): void
    {
        if ($exclude) {
            $query->whereDoesntHave('tags', fn (Builder $tags) => $tags->whereLike('tags.name', '%'.$term.'%'));

            return;
        }

        $query->whereHas('tags', fn (Builder $tags) => $tags->whereLike('tags.name', '%'.$term.'%'));
    }

    /**
     * Match a search term against the extracted text only.
     */
    private function applyTextMatch(Builder $query, string $term, bool $exclude): void
    {
        if ($exclude) {
            $query->whereNotLike('text', '%'.$term.'%');

            return;
        }

        $query->whereLike('text', '%'.$term.'%');
    }

    /**
     * Apply a date filter (since, until, or within) against the published
     * date. since/until dates are interpreted in the user's timezone and
     * converted to UTC to align with the stored naive UTC datetimes. Invalid
     * values are ignored so the rest of the search still applies.
     *
     * @param  string  $field     since | until | within
     * @param  string  $term      the raw date or duration value.
     * @param  string|null  $timezone  IANA timezone for since/until.
     */
    private function applyDateMatch(Builder $query, string $field, string $term, ?string $timezone = null): void
    {
        if ($field === 'within') {
            $duration = $this->parseDuration($term);

            if ($duration === null) {
                return;
            }

            $query->where('published_at', '>=', now()->sub($duration));

            return;
        }

        $date = $this->parseDate($term, $timezone);

        if ($date === null) {
            return;
        }

        if ($field === 'since') {
            $query->where('published_at', '>=', $date->startOfDay()->setTimezone('UTC'));

            return;
        }

        $query->where('published_at', '<=', $date->endOfDay()->setTimezone('UTC'));
    }

    /**
     * Parse a Y-m-d date string in the given IANA timezone, or null when the
     * value is not a valid date. The timezone defaults to UTC.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    private function parseDate(string $term, ?string $timezone = null): ?Carbon
    {
        $timezone = $timezone ?: 'UTC';

        try {
            return Carbon::createFromFormat('Y-m-d', $term, $timezone);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Parse a relative duration such as "5d", "2w", "12h", "3m", or "2y"
     * into a Carbon interval, or null when the value is not a valid duration.
     *
     * @return \Carbon\CarbonInterval|null
     */
    private function parseDuration(string $term): ?CarbonInterval
    {
        if (preg_match('/^(\d+)([dwhmy])$/i', $term, $match) !== 1) {
            return null;
        }

        $amount = (int) $match[1];
        $unit = strtolower($match[2]);

        return match ($unit) {
            'd' => CarbonInterval::days($amount),
            'w' => CarbonInterval::weeks($amount),
            'h' => CarbonInterval::hours($amount),
            'm' => CarbonInterval::months($amount),
            'y' => CarbonInterval::years($amount),
        };
    }

    /**
     * Match a search term against the title, description, extracted text,
     * or any tag name.
     */
    private function applyGeneralMatch(Builder $query, string $term, bool $exclude): void
    {
        $columns = fn (Builder $sub) => $sub
            ->whereLike('title', '%'.$term.'%')
            ->orWhereLike('description', '%'.$term.'%')
            ->orWhereLike('text', '%'.$term.'%')
            ->orWhereHas('tags', fn (Builder $tags) => $tags->whereLike('tags.name', '%'.$term.'%'));

        if ($exclude) {
            $query->whereNot($columns);

            return;
        }

        $query->where($columns);
    }

    /**
     * Normalize a search term for comparison against stored (normalized)
     * values. Tag names and extracted text preserve their casing for
     * LIKE matching, so this is a no-op except for trimming to the same
     * alphabet rules tags use.
     */
    private function normalizeSearchTerm(string $term): string
    {
        return trim($term);
    }

    /**
     * The tags attached to the sauce request, sorted alphabetically.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'sauce_request_tags')
            ->withTimestamps()
            ->orderBy('tags.name');
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
     * The extracted-text changes recorded against this sauce request,
     * newest first.
     */
    public function textHistory(): HasMany
    {
        return $this->hasMany(SauceRequestTextHistory::class)
            ->latest('id');
    }

    /**
     * The top-level comments on this sauce request, newest first.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(SauceRequestComment::class)
            ->whereNull('parent_id')
            ->latest('id');
    }

    /**
     * The sauce answers provided for this sauce request.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(SauceAnswer::class);
    }

    /**
     * The users who have bookmarked this sauce request to track its
     * progress.
     */
    public function bookmarks(): HasMany
    {
        return $this->hasMany(SauceRequestBookmark::class);
    }

    /**
     * The accepted sauce answer, or null when the request is unsolved.
     */
    public function acceptedAnswer(): BelongsTo
    {
        return $this->belongsTo(SauceAnswer::class, 'accepted_sauce');
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
