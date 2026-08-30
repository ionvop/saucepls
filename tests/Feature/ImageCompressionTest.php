<?php

use App\Services\ImageCompressionService;

/**
 * Build a real JPEG image at the given path so the compression service
 * has something to work with. Returns the path.
 */
function makeJpeg(string $path, int $width = 2000, int $height = 2000): string
{
    $image = imagecreatetruecolor($width, $height);

    // Fill with a noisy gradient so the JPEG does not compress to nothing.
    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            imagesetpixel($image, $x, $y, imagecolorallocate($image, $x % 255, $y % 255, ($x + $y) % 255));
        }
    }

    imagejpeg($image, $path, 100);
    imagedestroy($image);

    return $path;
}

// ---------------------------------------------------------------------------
// Service-level tests
// ---------------------------------------------------------------------------

it('converts a small image to WebP', function () {
    $path = tempnam(sys_get_temp_dir(), 'img');
    makeJpeg($path, 50, 50);

    $compressed = app(ImageCompressionService::class)->compressToWebpUnder($path, 1_000_000);

    expect($compressed)->toBeTrue();
    expect(getimagesize($path)['mime'])->toBe('image/webp');
});

it('skips animated GIFs to preserve animation', function () {
    $path = tempnam(sys_get_temp_dir(), 'img');
    file_put_contents($path, 'GIF89a'.str_repeat("\0", 100));

    $before = filesize($path);

    $compressed = app(ImageCompressionService::class)->compressToWebpUnder($path, 1_000_000);

    expect($compressed)->toBeFalse();
    expect(filesize($path))->toBe($before);
});

it('detects GIF files', function () {
    $path = tempnam(sys_get_temp_dir(), 'img');
    file_put_contents($path, 'GIF89a'.str_repeat("\0", 100));

    expect(app(ImageCompressionService::class)->isGif($path))->toBeTrue();
});

it('compresses a large image to under the target size', function () {
    $path = tempnam(sys_get_temp_dir(), 'img');
    makeJpeg($path);

    expect(filesize($path))->toBeGreaterThanOrEqual(1_000_000);

    $compressed = app(ImageCompressionService::class)->compressToWebpUnder($path, 1_000_000);

    expect($compressed)->toBeTrue();
    expect(filesize($path))->toBeLessThan(1_000_000);
});

it('replaces the file with a WebP image', function () {
    $path = tempnam(sys_get_temp_dir(), 'img');
    makeJpeg($path);

    app(ImageCompressionService::class)->compressToWebpUnder($path, 1_000_000);

    $info = getimagesize($path);

    expect($info['mime'])->toBe('image/webp');
});
