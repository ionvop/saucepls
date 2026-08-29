<?php

namespace App\Services;

/**
 * Runs a reverse image lookup against the SauceNAO API to try to
 * identify the source of an image automatically.
 *
 * @todo Implement the SauceNAO API integration (see docs/saucenao-example.md).
 */
class SauceNaoService
{
    /**
     * Look up the source of the image at the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     * @return array<int, array<string, mixed>>  A list of matched sources.
     */
    public function lookup(string $path): array
    {
        // TODO: Replace with a real SauceNAO API call.
        return [];
    }
}