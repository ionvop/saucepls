<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'brevo' => [
        'key' => env('BREVO_API_KEY'),
        'from_address' => env('BREVO_FROM_ADDRESS', 'noreply@saucepls.app'),
        'from_name' => env('BREVO_FROM_NAME', 'SaucePls'),
    ],

    'duplicate' => [
        // The maximum Hamming distance between two 64-bit perceptual
        // hashes for the images to be considered duplicates.
        'phash_threshold' => env('DUPLICATE_PHASH_THRESHOLD', 10),
    ],

    'saucenao' => [
        // API key for the SauceNAO reverse image search. Get one from
        // https://saucenao.com/user.php
        'key' => env('SAUCENAO_API_KEY'),

        // The SauceNAO search endpoint.
        'endpoint' => env('SAUCENAO_ENDPOINT', 'https://saucenao.com/search.php'),

        // How many results to request from SauceNAO.
        'numres' => env('SAUCENAO_NUMRES', 5),

        // The minimum similarity percentage for a result to be considered
        // an "easily identifiable" match. Results below this are ignored.
        'min_similarity' => env('SAUCENAO_MIN_SIMILARITY', 60),

        // How long (in minutes) to cache a lookup result so the
        // intermediate page does not re-hit the SauceNAO API.
        'cache_ttl' => env('SAUCENAO_CACHE_TTL', 10),
    ],

    'ocr' => [
        // API key for the OCR.space OCR API. Get a free one from
        // https://ocr.space/ocrapi/freekey
        'key' => env('OCR_SPACE_API_KEY'),

        // The OCR.space parse endpoint.
        'endpoint' => env('OCR_SPACE_ENDPOINT', 'https://api.ocr.space/parse/image'),

        // Language used for OCR. "auto" lets the API detect it (Engine 2/3).
        'language' => env('OCR_SPACE_LANGUAGE', 'auto'),

        // The OCR engine to use. Engine 2 is the default and best all-round
        // choice for noisy/photo backgrounds like animanga images.
        'engine' => env('OCR_SPACE_ENGINE', 2),
    ],

    'drafts' => [
        // Unpublished drafts abandoned before the user clicks "Post request"
        // are purged opportunistically once they exceed this age.
        'ttl_hours' => env('DRAFTS_TTL_HOURS', 24),
    ],

    'image_compression' => [
        // Whether uploaded images are compressed to WebP before the
        // pre-post pipeline runs.
        'enabled' => env('IMAGE_COMPRESSION_ENABLED', true),

        // Images at or above this size (in bytes) are compressed. The
        // original is discarded and replaced with the compressed WebP.
        'max_bytes' => env('IMAGE_COMPRESSION_MAX_BYTES', 1_000_000),

        // The lowest WebP quality to try before scaling the image down.
        'min_quality' => env('IMAGE_COMPRESSION_MIN_QUALITY', 30),

        // How much to scale the image down on each retry when quality
        // alone is not enough to reach the target size.
        'scale_factor' => env('IMAGE_COMPRESSION_SCALE_FACTOR', 0.9),
    ],

];
