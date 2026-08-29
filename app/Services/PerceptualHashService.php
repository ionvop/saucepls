<?php

namespace App\Services;

/**
 * Computes a perceptual hash (phash) for an image so that duplicate
 * sauce requests can be detected via reverse image search.
 *
 * @todo Implement a real perceptual hashing algorithm (e.g. via the
 *       `intervention/image` package or a dedicated phash library).
 */
class PerceptualHashService
{
    /**
     * Compute a 64-bit perceptual hash for the image at the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     */
    public function hash(string $path): string
    {
        // TODO: Replace with a real perceptual hash implementation.
        return hash('sha256', $path . ':' . (string) filemtime($path));
    }
}