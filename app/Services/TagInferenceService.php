<?php

namespace App\Services;

/**
 * Sends an image to an external model inference API (DeepDanbooru-style)
 * to automatically suggest tags for the image.
 *
 * @todo Implement the model inference API integration
 *       (see docs/deepdanbooru-example.md).
 */
class TagInferenceService
{
    /**
     * Infer a list of suggested tags for the image at the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     * @return array<int, string>  A list of suggested tag names.
     */
    public function infer(string $path): array
    {
        // TODO: Replace with a real model inference API call.
        return [];
    }
}