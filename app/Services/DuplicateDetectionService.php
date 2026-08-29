<?php

namespace App\Services;

use App\Models\SauceRequest;

/**
 * Detects whether an uploaded image is a near-duplicate of an existing
 * sauce request by comparing their perceptual hashes.
 */
class DuplicateDetectionService
{
    public function __construct(
        private readonly PerceptualHashService $perceptualHash,
    ) {}

    /**
     * Find the closest existing sauce request whose perceptual hash is
     * within the configured Hamming distance threshold, or null when no
     * duplicate is found.
     *
     * @param  int|null  $excludeId  A sauce request id to skip, e.g. the
     *                               draft that was just created for the
     *                               uploaded image.
     */
    public function findDuplicate(string $phash, ?int $excludeId = null): ?SauceRequest
    {
        $threshold = (int) config('services.duplicate.phash_threshold', 10);

        $closest = null;
        $closestDistance = PHP_INT_MAX;

        // Only published requests can be flagged as duplicates. Unpublished
        // drafts are still in the pre-post pipeline and must not match.
        foreach (SauceRequest::query()->published()->get() as $sauceRequest) {
            if ($sauceRequest->id === $excludeId) {
                continue;
            }

            $distance = $this->perceptualHash->distance($phash, $sauceRequest->phash64);

            if ($distance <= $threshold && $distance < $closestDistance) {
                $closest = $sauceRequest;
                $closestDistance = $distance;
            }
        }

        return $closest;
    }
}
