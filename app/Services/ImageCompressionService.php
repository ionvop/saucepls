<?php

namespace App\Services;

/**
 * Compresses uploaded images to WebP so they stay under a target file
 * size before the pre-post pipeline (perceptual hashing, OCR, tag
 * inference, and SauceNAO) runs against them.
 *
 * Every non-GIF image is converted to WebP, regardless of its original
 * size, so all stored images end up as WebP under the target size. GIFs
 * are skipped to preserve their animation; they bypass the pipeline
 * entirely and are stored as-is. The original file is replaced in place
 * so the rest of the upload flow keeps using the same path.
 */
class ImageCompressionService
{
    /**
     * Whether the image at the given path is a GIF.
     *
     * @param  string  $path  Absolute path to the image file.
     */
    public function isGif(string $path): bool
    {
        return $this->mimeType($path) === 'image/gif';
    }

    /**
     * Compress the image at the given path to WebP, scaling it down
     * until it fits under the target size.
     *
     * @param  string  $path  Absolute path to the image file.
     * @param  int  $maxBytes  Target maximum file size in bytes.
     * @return bool Whether the file was compressed (false if it was a
     *              GIF, could not be processed, or compression is
     *              disabled).
     */
    public function compressToWebpUnder(string $path, int $maxBytes = 1_000_000): bool
    {
        if (! $this->enabled()) {
            return false;
        }

        if (! is_file($path)) {
            return false;
        }

        $mime = $this->mimeType($path);

        // Skip GIFs so their animation is preserved.
        if ($mime === 'image/gif') {
            return false;
        }

        $image = $this->load($path, $mime);

        if ($image === null) {
            logger()->warning('ImageCompression: unsupported image type', [
                'path' => $path,
                'mime' => $mime,
            ]);

            return false;
        }

        try {
            $compressed = $this->encodeToWebp($image, $maxBytes);

            if ($compressed === null) {
                return false;
            }

            // Write to a temp file first, then atomically replace the
            // original so the path stays valid for the pipeline.
            $temp = tempnam(dirname($path), 'webp');

            if ($temp === false) {
                return false;
            }

            file_put_contents($temp, $compressed);
            rename($temp, $path);

            return true;
        } finally {
            imagedestroy($image);
        }
    }

    /**
     * Whether image compression is enabled.
     */
    protected function enabled(): bool
    {
        return (bool) config('services.image_compression.enabled', true);
    }

    /**
     * Detect the MIME type of the image at the given path.
     */
    protected function mimeType(string $path): ?string
    {
        $info = @getimagesize($path);

        return $info['mime'] ?? null;
    }

    /**
     * Load the image into a GD resource based on its MIME type.
     *
     * Palette (indexed) images are converted to truecolor because
     * imagewebp() cannot encode palette images.
     *
     * @return \GdImage|null
     */
    protected function load(string $path, ?string $mime)
    {
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png' => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default => null,
        };

        if ($image === null) {
            return null;
        }

        return $this->toTrueColor($image);
    }

    /**
     * Convert a palette (indexed) image to truecolor so it can be
     * encoded to WebP. Truecolor images are returned unchanged.
     *
     * @param  \GdImage  $image
     * @return \GdImage
     */
    protected function toTrueColor($image)
    {
        if (imageistruecolor($image)) {
            return $image;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $trueColor = imagecreatetruecolor($width, $height);

        if ($trueColor === false) {
            return $image;
        }

        // Preserve transparency for images that have it.
        imagealphablending($trueColor, false);
        imagesavealpha($trueColor, true);

        imagecopy($trueColor, $image, 0, 0, 0, 0, $width, $height);

        imagedestroy($image);

        return $trueColor;
    }

    /**
     * Encode the image to WebP, iteratively lowering the quality and
     * scaling it down until the output fits under the target size.
     *
     * @param  \GdImage  $image
     * @return string|null The WebP binary data, or null if it could not
     *                     be encoded under the target size.
     */
    protected function encodeToWebp($image, int $maxBytes): ?string
    {
        if (! function_exists('imagewebp')) {
            logger()->warning('ImageCompression: WebP support is not available');

            return null;
        }

        $minQuality = (int) config('services.image_compression.min_quality', 30);
        $scaleFactor = (float) config('services.image_compression.scale_factor', 0.9);

        $width = imagesx($image);
        $height = imagesy($image);

        for ($quality = 90; $quality >= $minQuality; $quality -= 10) {
            $data = $this->encode($image, $quality);

            if ($data !== null && strlen($data) < $maxBytes) {
                return $data;
            }
        }

        // Quality alone was not enough; scale the image down and retry.
        $current = $image;

        while ($width > 1 && $height > 1) {
            $width = max(1, (int) floor($width * $scaleFactor));
            $height = max(1, (int) floor($height * $scaleFactor));

            $scaled = imagecreatetruecolor($width, $height);

            if ($scaled === false) {
                break;
            }

            imagecopyresampled($scaled, $current, 0, 0, 0, 0, $width, $height, imagesx($current), imagesy($current));

            if ($current !== $image) {
                imagedestroy($current);
            }

            $current = $scaled;

            for ($quality = 90; $quality >= $minQuality; $quality -= 10) {
                $data = $this->encode($current, $quality);

                if ($data !== null && strlen($data) < $maxBytes) {
                    if ($current !== $image) {
                        imagedestroy($current);
                    }

                    return $data;
                }
            }
        }

        if ($current !== $image) {
            imagedestroy($current);
        }

        return null;
    }

    /**
     * Encode a GD image to WebP at the given quality.
     *
     * @param  \GdImage  $image
     */
    protected function encode($image, int $quality): ?string
    {
        ob_start();

        if (! imagewebp($image, null, $quality)) {
            ob_end_clean();

            return null;
        }

        return (string) ob_get_clean();
    }
}
