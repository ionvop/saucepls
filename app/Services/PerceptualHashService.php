<?php

namespace App\Services;

use Jenssegers\ImageHash\Hash;
use Jenssegers\ImageHash\ImageHash;
use Jenssegers\ImageHash\Implementations\DifferenceHash;

/**
 * Computes a perceptual hash (phash) for an image so that duplicate
 * sauce requests can be detected via reverse image search.
 *
 * Uses the `jenssegers/imagehash` package with the DifferenceHash
 * implementation, which produces a 64-bit fingerprint that can be
 * compared with a Hamming distance.
 */
class PerceptualHashService
{
    private readonly ImageHash $hasher;

    public function __construct()
    {
        $this->hasher = new ImageHash(new DifferenceHash);
    }

    /**
     * Compute a 64-bit perceptual hash for the image at the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     */
    public function hash(string $path): string
    {
        return $this->hasher->hash($path)->toHex();
    }

    /**
     * Compute the Hamming distance between two perceptual hashes.
     *
     * @param  string  $a  A hex-encoded perceptual hash.
     * @param  string  $b  A hex-encoded perceptual hash.
     */
    public function distance(string $a, string $b): int
    {
        return $this->hashFromHex($a)->distance($this->hashFromHex($b));
    }

    /**
     * Reconstruct a Hash object from a hex-encoded perceptual hash.
     *
     * The library only exposes `Hash::fromBits()`, so the hex string is
     * converted to its binary representation first.
     */
    private function hashFromHex(string $hex): Hash
    {
        $bits = '';

        foreach (str_split($hex, 2) as $byte) {
            $bits .= str_pad(decbin(hexdec($byte)), 8, '0', STR_PAD_LEFT);
        }

        return Hash::fromBits($bits);
    }
}
