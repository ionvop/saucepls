<?php

namespace App\Services;

use App\Models\SauceRequest;
use App\Models\SauceRequestTaggingHistory;
use App\Models\Tag;
use App\Models\User;

/**
 * Normalizes, attaches, and detaches tags on sauce requests while
 * recording each change in the tagging history for attribution.
 *
 * Tag normalization rules (see docs/proposal.md):
 *  - Tags are separated by spaces.
 *  - Tags are lowercase.
 *  - Only alphanumeric, hyphens, and underscores are kept.
 *  - Leading hyphens are stripped (they are used for search exclusions).
 *  - Duplicates are ignored.
 */
class TagService
{
    /**
     * Normalize a raw, space-separated tag string (or list of names) into
     * a unique list of valid tag names.
     *
     * @param  string|array<int, string>  $tags
     * @return array<int, string>
     */
    public function normalize(string|array $tags): array
    {
        $tags = is_array($tags) ? $tags : (preg_split('/\s+/', $tags) ?: []);

        $names = [];

        foreach ($tags as $tag) {
            $tag = mb_strtolower((string) $tag);
            $tag = preg_replace('/[^a-z0-9_-]/', '', $tag) ?? '';
            $tag = ltrim($tag, '-');

            if ($tag === '') {
                continue;
            }

            // Ignore duplicates.
            $names[$tag] = true;
        }

        return array_keys($names);
    }

    /**
     * Bring a sauce request's tags in line with the given names,
     * adding and removing only what differs.
     *
     * @param  string|array<int, string>  $names
     */
    public function sync(SauceRequest $sauceRequest, string|array $names, User $user): void
    {
        $names = $this->normalize($names);

        $current = $sauceRequest->tags()->pluck('tags.name')->all();

        $toAdd = array_values(array_diff($names, $current));
        $toRemove = array_values(array_diff($current, $names));

        if ($toAdd === [] && $toRemove === []) {
            return;
        }

        $added = [];

        foreach ($toAdd as $name) {
            $tag = Tag::firstOrCreate(['name' => $name]);

            $sauceRequest->tags()->attach($tag);
            $added[] = $name;
        }

        $removed = [];

        if ($toRemove !== []) {
            $tagIds = Tag::whereIn('name', $toRemove)->pluck('id')->all();

            $sauceRequest->tags()->detach($tagIds);
            $removed = $toRemove;
        }

        $this->record($sauceRequest, $user, added: $added, removed: $removed);
    }

    /**
     * Attach the given tag names to the sauce request, creating any tags
     * that do not exist yet, and record the change in the history.
     *
     * @param  string|array<int, string>  $names
     */
    public function add(SauceRequest $sauceRequest, string|array $names, User $user): void
    {
        $names = $this->normalize($names);

        if ($names === []) {
            return;
        }

        $existing = $sauceRequest->tags()->pluck('tags.name')->all();

        $added = [];

        foreach ($names as $name) {
            if (in_array($name, $existing, true)) {
                continue;
            }

            $tag = Tag::firstOrCreate(['name' => $name]);

            $sauceRequest->tags()->attach($tag);
            $added[] = $name;
        }

        if ($added === []) {
            return;
        }

        $this->record($sauceRequest, $user, added: $added);
    }

    /**
     * Detach the given tag names from the sauce request and record the
     * change in the history.
     *
     * @param  string|array<int, string>  $names
     */
    public function remove(SauceRequest $sauceRequest, string|array $names, User $user): void
    {
        $names = $this->normalize($names);

        if ($names === []) {
            return;
        }

        $current = $sauceRequest->tags()->pluck('tags.name')->all();
        $removed = array_values(array_intersect($names, $current));

        if ($removed === []) {
            return;
        }

        $tagIds = Tag::whereIn('name', $removed)->pluck('id')->all();

        $sauceRequest->tags()->detach($tagIds);

        $this->record($sauceRequest, $user, removed: $removed);
    }

    /**
     * Undo a single recorded tagging change by re-attaching the tags that
     * were removed and detaching the tags that were added. The reversal is
     * itself recorded in the history to preserve the audit trail.
     */
    public function revert(SauceRequestTaggingHistory $history, User $user): void
    {
        $sauceRequest = $history->sauceRequest;

        $current = $sauceRequest->tags()->pluck('tags.name')->all();

        // Re-attach the tags that the change removed, and detach the tags
        // that the change added. Only act on tags that are actually present
        // or absent so a no-op revert does not record anything.
        $toAdd = array_values(array_diff($history->removed_tags ?? [], $current));
        $toRemove = array_values(array_intersect($history->added_tags ?? [], $current));

        if ($toAdd === [] && $toRemove === []) {
            return;
        }

        $added = [];

        foreach ($toAdd as $name) {
            $tag = Tag::firstOrCreate(['name' => $name]);

            $sauceRequest->tags()->attach($tag);
            $added[] = $name;
        }

        $removed = [];

        if ($toRemove !== []) {
            $tagIds = Tag::whereIn('name', $toRemove)->pluck('id')->all();

            $sauceRequest->tags()->detach($tagIds);
            $removed = $toRemove;
        }

        $this->record($sauceRequest, $user, added: $added, removed: $removed);
    }

    /**
     * Restore a sauce request's tags to the state they were in immediately
     * after the given history entry was applied. Because history rows only
     * store per-change diffs, the target state is reconstructed by folding
     * every change up to and including the target forward from an empty set.
     * The resulting change is recorded in the history for attribution.
     */
    public function restoreTo(SauceRequest $sauceRequest, SauceRequestTaggingHistory $target, User $user): void
    {
        $state = [];

        $history = SauceRequestTaggingHistory::query()
            ->where('sauce_request_id', $sauceRequest->id)
            ->where('id', '<=', $target->id)
            ->orderBy('id')
            ->get();

        foreach ($history as $entry) {
            $state = array_values(array_diff($state, $entry->removed_tags ?? []));
            $state = array_values(array_unique(array_merge($state, $entry->added_tags ?? [])));
        }

        $this->sync($sauceRequest, $state, $user);
    }

    /**
     * Persist a tagging change for attribution.
     *
     * @param  array<int, string>  $added
     * @param  array<int, string>  $removed
     */
    private function record(SauceRequest $sauceRequest, User $user, array $added = [], array $removed = []): void
    {
        if ($added === [] && $removed === []) {
            return;
        }

        SauceRequestTaggingHistory::create([
            'sauce_request_id' => $sauceRequest->id,
            'user_id' => $user->id,
            'added_tags' => $added,
            'removed_tags' => $removed,
        ]);
    }
}
