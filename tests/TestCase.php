<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Http;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fail fast if any test sends a real HTTP request. This catches
        // forgotten---or missing---`Http::fake()` / service mocks so tests
        // never hit third-party APIs (e.g. SauceNAO, OCR.space) and consume
        // live quota.
        Http::preventStrayRequests();
    }
}
