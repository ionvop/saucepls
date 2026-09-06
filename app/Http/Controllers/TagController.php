<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends Controller
{
    /**
     * Return tag autocomplete suggestions for a search prefix.
     *
     * Accepts a single `q` parameter (the tag name prefix). Tags whose names
     * start with the prefix are returned as JSON, ordered first by the number
     * of sauce requests they are used on (descending) and then alphabetically.
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:40'],
        ]);

        $term = trim((string) $request->query('q', ''));

        // A very short prefix would return most of the tag table; require at
        // least two characters so suggestions are actually meaningful.
        if (mb_strlen($term) < 2) {
            return response()->json(['tags' => []]);
        }

        $tags = Tag::query()
            ->autocomplete($term)
            ->limit(10)
            ->get(['id', 'name', 'usage_count']);

        return response()->json([
            'tags' => $tags->map(fn (Tag $tag) => [
                'id' => $tag->id,
                'name' => $tag->name,
                'usage_count' => (int) $tag->usage_count,
            ]),
        ]);
    }
}