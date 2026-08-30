<?php

use App\Models\SauceRequest;
use App\Models\User;
use App\Services\DuplicateDetectionService;
use App\Services\OcrService;
use App\Services\SauceNaoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

/**
 * Build a fake OCR.space API response payload.
 */
function fakeOcrPayload(string $parsedText, bool $errored = false, ?string $errorMessage = null): array
{
    return [
        'ParsedResults' => [
            [
                'FileParseExitCode' => $errored ? -10 : 1,
                'ParsedText' => $parsedText,
                'ErrorMessage' => $errorMessage,
            ],
        ],
        'OCRExitCode' => $errored ? 4 : 1,
        'IsErroredOnProcessing' => $errored,
        'ErrorMessage' => $errorMessage,
        'ErrorDetails' => $errorMessage ? 'Some details' : null,
    ];
}

// ---------------------------------------------------------------------------
// Service-level tests
// ---------------------------------------------------------------------------

it('returns the parsed text from a successful OCR response', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr');

    Http::fake([
        'api.ocr.space/*' => Http::response(fakeOcrPayload("Hello\nWorld")),
    ]);

    $text = app(OcrService::class)->extractText($path);

    expect($text)->toBe("Hello\nWorld");
});

it('trims surrounding whitespace from the parsed text', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr');

    Http::fake([
        'api.ocr.space/*' => Http::response(fakeOcrPayload("  Hello World  \n")),
    ]);

    $text = app(OcrService::class)->extractText($path);

    expect($text)->toBe('Hello World');
});

it('returns an empty string when the API reports an error', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr');

    Http::fake([
        'api.ocr.space/*' => Http::response(fakeOcrPayload('', errored: true, errorMessage: 'Invalid API key')),
    ]);

    $text = app(OcrService::class)->extractText($path);

    expect($text)->toBe('');
});

it('returns an empty string when the API request fails', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr');

    Http::fake([
        'api.ocr.space/*' => Http::response('Server error', 500),
    ]);

    $text = app(OcrService::class)->extractText($path);

    expect($text)->toBe('');
});

it('sends the image as a multipart file upload with the api key header', function () {
    $path = tempnam(sys_get_temp_dir(), 'ocr');

    Http::fake();

    app(OcrService::class)->extractText($path);

    Http::assertSent(function ($request) {
        return $request->url() === 'https://api.ocr.space/parse/image'
            && $request->hasHeader('apikey')
            && $request->hasFile('file');
    });
});

// ---------------------------------------------------------------------------
// Feature-level tests
// ---------------------------------------------------------------------------

it('stores the OCR text as the initial value of the text field', function () {
    $user = User::factory()->create();

    $this->mock(DuplicateDetectionService::class)
        ->shouldReceive('findDuplicate')
        ->once()
        ->andReturn(null);

    $this->mock(SauceNaoService::class)
        ->shouldReceive('lookup')
        ->once()
        ->andReturn([]);

    Http::fake([
        'api.ocr.space/*' => Http::response(fakeOcrPayload('Extracted text from image')),
    ]);

    $this->actingAs($user)
        ->post(route('sauce-requests.upload'), [
            'title' => 'Who drew this?',
            'image' => UploadedFile::fake()->image('art.png'),
        ])
        ->assertRedirect(route('sauce-requests.details', SauceRequest::firstOrFail()));

    $sauceRequest = SauceRequest::firstOrFail();

    expect($sauceRequest->text)->toBe('Extracted text from image');

    $this->get(route('sauce-requests.details', $sauceRequest))
        ->assertOk()
        ->assertSee('Extracted text from image');
});
