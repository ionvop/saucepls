<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Scans an image with OCR to automatically extract any text it contains.
 *
 * Uses the OCR.space cloud API (https://ocr.space/ocrapi) so no local OCR
 * binary is required. The extracted text is used to pre-fill the request's
 * `text` column on the details page.
 */
class OcrService
{
    /**
     * The OCR.space parse endpoint.
     */
    protected const ENDPOINT = 'https://api.ocr.space/parse/image';

    /**
     * Extract text from the image at the given path.
     *
     * @param  string  $path  Absolute path to the image file.
     */
    public function extractText(string $path): string
    {
        $response = Http::asMultipart()
            ->withHeaders([
                'apikey' => config('services.ocr.key'),
            ])
            ->post(config('services.ocr.endpoint', self::ENDPOINT), [
                'file' => fopen($path, 'r'),
                'language' => config('services.ocr.language', 'auto'),
                'OCREngine' => config('services.ocr.engine', 2),
                'detectOrientation' => true,
                'scale' => true,
                'isOverlayRequired' => false,
            ]);

        if (! $response->successful()) {
            logger()->error('OCR.space request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return '';
        }

        $payload = $response->json();

        // The API reports a fatal error via these top-level fields. Treat
        // it as "no text found" so the upload can continue with an empty
        // text field rather than failing the whole request.
        if (($payload['IsErroredOnProcessing'] ?? false) || ! empty($payload['ErrorMessage'])) {
            logger()->error('OCR.space reported an error', [
                'message' => $payload['ErrorMessage'] ?? null,
                'details' => $payload['ErrorDetails'] ?? null,
            ]);

            return '';
        }

        $text = trim((string) ($payload['ParsedResults'][0]['ParsedText'] ?? ''));

        return $text;
    }
}
