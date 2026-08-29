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
     */
    public function findDuplicate(string $phash): ?SauceRequest
    {
        $threshold = (int) config('services.duplicate.phash_threshold', 10);

        $closest = null;
        $closestDistance = PHP_INT_MAX;

        foreach (SauceRequest::query()->get() as $sauceRequest) {
            $distance = $this->perceptualHash->distance($phash, $sauceRequest->phash64);

            if ($distance <= $threshold && $distance < $closestDistance) {
                $closest = $sauceRequest;
                $closestDistance = $distance;
            }
        }

        return $closest;
    }
}
