<?php

namespace App\Services;

/**
 * Scans an image with OCR to automatically extract any text it contains.
 *
 * @todo Implement OCR (e.g. via Tesseract) to populate the request's
 *       `text` column.
 */
class OcrService
{
    /**
     * Extract text from the image at the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     */
    public function extractText(string $path): string
    {
        // TODO: Replace with a real OCR implementation.
        return '';
    }
}