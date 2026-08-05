<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BrevoMailService
{
    /**
     * The Brevo Transactional Email API endpoint.
     */
    protected const ENDPOINT = 'https://api.brevo.com/v3/smtp/email';

    /**
     * Send a transactional email through the Brevo API.
     *
     * @param  string  $to  Recipient email address.
     * @param  string  $subject  Email subject.
     * @param  string  $html  HTML body of the email.
     * @param  string|null  $text  Optional plain-text body.
     */
    public function send(string $to, string $subject, string $html, ?string $text = null): bool
    {
        $payload = [
            'sender' => [
                'name' => config('services.brevo.from_name'),
                'email' => config('services.brevo.from_address'),
            ],
            'to' => [
                ['email' => $to],
            ],
            'subject' => $subject,
            'htmlContent' => $html,
        ];

        if ($text !== null) {
            $payload['textContent'] = $text;
        }

        $response = Http::withHeaders([
                'api-key' => config('services.brevo.key'),
            ])
            ->acceptJson()
            ->post(self::ENDPOINT, $payload);

        if (! $response->successful()) {
            logger()->error('Brevo send failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
                'to'     => $to,
            ]);
        }

        return $response->successful();
    }
}
