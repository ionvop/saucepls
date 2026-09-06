<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Sends an image to an external model inference API (DeepDanbooru-style)
 * to automatically suggest tags for the image.
 *
 * The API is POSTed the image as a multipart file upload and returns a
 * list of tags with confidence scores. Tags below the configured
 * threshold, and rating tags (e.g. "rating:safe"), are discarded from the
 * suggested-tag list. The remaining tag names are used to pre-fill the
 * request's tags field on the details page.
 *
 * The rating tag and its confidence are still surfaced via
 * {@see self::inferWithRating()} so callers can detect a confident
 * "rating:safe" result (e.g. to auto-clear the explicit flag).
 *
 * @see docs/deepdanbooru-example.md
 */
class TagInferenceService
{
    /**
     * The model inference endpoint.
     */
    protected const ENDPOINT = 'https://deepdanbooru.nsk.sh/deepdanbooru';

    /**
     * The prefix of rating tags returned by the inference API (e.g.
     * "rating:safe", "rating:explicit", "rating:questionable").
     */
    protected const RATING_PREFIX = 'rating:';

    /**
     * Infer a list of suggested tags for the image at the given path.
     *
     * Rating tags are excluded from the returned list.
     *
     * @param  string  $path  Absolute path to the image file.
     * @return array<int, string>  A list of suggested tag names.
     */
    public function infer(string $path): array
    {
        return $this->inferWithRating($path)['tags'];
    }

    /**
     * Infer the suggested tags and the confidence rating for the image at
     * the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     * @return array{
     *     tags: array<int, string>,
     *     rating: string|null,
     *     rating_confidence: float|null,
     * }
     */
    public function inferWithRating(string $path): array
    {
        $response = Http::asMultipart()
            ->withQueryParameters([
                'threshold' => config('services.tag_inference.threshold', 0.2),
            ])
            ->post(config('services.tag_inference.endpoint', self::ENDPOINT), [
                'image' => fopen($path, 'r'),
            ]);

        if (! $response->successful()) {
            logger()->error('Tag inference request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'tags' => [],
                'rating' => null,
                'rating_confidence' => null,
            ];
        }

        $threshold = (float) config('services.tag_inference.threshold', 0.2);
        $maxTags = config('services.tag_inference.max_tags');

        $items = collect($response->json() ?? []);

        // The rating is the highest-scoring "rating:*" item above the
        // threshold, so the most confident classification wins.
        $rating = $items
            ->filter(fn (array $item) => ($item['score'] ?? 0) >= $threshold)
            ->filter(fn (array $item) => str_starts_with((string) ($item['tag'] ?? ''), self::RATING_PREFIX))
            ->sortByDesc(fn (array $item) => (float) ($item['score'] ?? 0))
            ->first();

        $tags = $items
            ->filter(fn (array $item) => ($item['score'] ?? 0) >= $threshold)
            ->reject(fn (array $item) => str_starts_with((string) ($item['tag'] ?? ''), self::RATING_PREFIX))
            ->map(fn (array $item) => (string) $item['tag'])
            ->values();

        if ($maxTags !== null) {
            $tags = $tags->take((int) $maxTags);
        }

        return [
            'tags' => $tags->all(),
            'rating' => $rating !== null
                ? substr((string) $rating['tag'], strlen(self::RATING_PREFIX))
                : null,
            'rating_confidence' => $rating !== null
                ? (float) $rating['score']
                : null,
        ];
    }
}