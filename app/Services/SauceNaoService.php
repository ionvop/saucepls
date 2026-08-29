<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Runs a reverse image lookup against the SauceNAO API to try to
 * identify the source of an image automatically.
 *
 * @see docs/saucenao-example.md
 */
class SauceNaoService
{
    /**
     * The SauceNAO search endpoint.
     */
    protected const ENDPOINT = 'https://saucenao.com/search.php';

    /**
     * Look up the source of the image at the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     * @return array<int, array<string, mixed>>  A list of matched sources,
     *                                           normalized and filtered to
     *                                           those above the configured
     *                                           minimum similarity.
     */
    public function lookup(string $path): array
    {
        $response = Http::asMultipart()
            ->acceptJson()
            ->post(self::ENDPOINT, [
                'api_key' => config('services.saucenao.key'),
                'output_type' => 2,
                'numres' => config('services.saucenao.numres', 5),
                'file' => fopen($path, 'r'),
            ]);

        if (! $response->successful()) {
            logger()->error('SauceNAO lookup failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [];
        }

        $payload = $response->json();

        // A non-zero header status indicates an error (e.g. invalid key,
        // rate limit, or no results). Treat it as "not identifiable".
        if (($payload['header']['status'] ?? 0) !== 0) {
            return [];
        }

        $minimumSimilarity = (float) config('services.saucenao.min_similarity', 60);

        return collect($payload['results'] ?? [])
            ->map(fn (array $result) => $this->normalize($result))
            ->filter(fn (array $result) => $result['similarity'] >= $minimumSimilarity)
            ->values()
            ->all();
    }

    /**
     * Normalize a single SauceNAO result into a consistent shape.
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    protected function normalize(array $result): array
    {
        $header = $result['header'] ?? [];
        $data = $result['data'] ?? [];

        return [
            'similarity' => (float) ($header['similarity'] ?? 0),
            'thumbnail' => $header['thumbnail'] ?? null,
            'index_id' => (int) ($header['index_id'] ?? 0),
            'index_name' => $header['index_name'] ?? null,
            'urls' => $data['ext_urls'] ?? [],
            'title' => $this->toString($data['title'] ?? $data['eng_name'] ?? $data['source'] ?? null),
            'author' => $this->toString($data['member_name'] ?? $data['creator'] ?? $data['author_name'] ?? null),
        ];
    }

    /**
     * Coerce a SauceNAO field value into a plain string.
     *
     * Some fields (e.g. "creator") can be an array of names, which we
     * join into a single comma-separated string so the value is always
     * safe to echo in a view.
     *
     * @param  mixed  $value
     */
    protected function toString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            $value = implode(', ', array_filter($value, fn ($item) => is_scalar($item)));
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}