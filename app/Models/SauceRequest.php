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
     *  - Exclusions: a leading hyphen (e.g. -kitty) excludes requests that
     *    contain the word anywhere.
     *
     * @param  string|null  $search
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        foreach ($this->tokenizeSearch($search) as $word) {
            $query->where(function (Builder $sub) use ($word) {
                $this->applyWordMatches($sub, $word);
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

        if (preg_match_all('/"([^"]*)"|(\S+)/', $search, $matches, PREG_SET_ORDER) === false) {
            return $words;
        }

        foreach ($matches as $match) {
            $raw = $match[1] !== '' ? $match[1] : $match[2];

            if ($raw === '') {
                continue;
            }

            $exclude = str_starts_with($raw, '-');
            $field = null;

            if (preg_match('/^(tag|text):"([^"]*)"$/i', $raw, $typed) === 1) {
                $field = strtolower($typed[1]);
                $raw = $typed[2];
                $exclude = false;
            } elseif (preg_match('/^(tag|text):(\S+)$/i', $raw, $typed) === 1) {
                $field = strtolower($typed[1]);
                $raw = $typed[2];
            } elseif ($exclude) {
                $raw = ltrim($raw, '-');
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
     */
    private function applyWordMatches(Builder $query, array $word): void
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
