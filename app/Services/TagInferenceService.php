<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Sends an image to an external model inference API (DeepDanbooru-style)
 * to automatically suggest tags for the image.
 *
 * The API is POSTed the image as a multipart file upload and returns a
 * list of tags with confidence scores. Tags below the configured
 * threshold, and rating tags (e.g. "rating:safe"), are discarded. The
 * remaining tag names are used to pre-fill the request's tags field on
 * the details page.
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
     * Infer a list of suggested tags for the image at the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     * @return array<int, string>  A list of suggested tag names.
     */
    public function infer(string $path): array
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

            return [];
        }

        $threshold = (float) config('services.tag_inference.threshold', 0.2);
        $maxTags = config('services.tag_inference.max_tags');

        $tags = collect($response->json() ?? [])
            ->filter(fn (array $item) => ($item['score'] ?? 0) >= $threshold)
            ->filter(fn (array $item) => ! str_starts_with((string) ($item['tag'] ?? ''), 'rating:'))
            ->map(fn (array $item) => (string) $item['tag'])
            ->values();

        if ($maxTags !== null) {
            $tags = $tags->take((int) $maxTags);
        }

        return $tags->all();
    }
}