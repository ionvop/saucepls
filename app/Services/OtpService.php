<?php

namespace App\Services;

use App\Models\EmailCode;

class OtpService
{
    /**
     * How long (in minutes) a code remains valid.
     */
    public const TTL_MINUTES = 5;

    /**
     * Generate a fresh 6-digit code for the given email, store its hash,
     * and return the plaintext code so it can be emailed to the user.
     */
    public function generate(string $email): string
    {
        $code = (string) random_int(100000, 999999);

        EmailCode::query()
            ->where('email', $email)
            ->delete();

        EmailCode::create([
            'email' => $email,
            'code_hash' => bcrypt($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
        ]);

        return $code;
    }

    /**
     * Verify a submitted code against the most recent code for the email.
     * Consumes the code on success so it cannot be reused.
     */
    public function verify(string $email, string $code): bool
    {
        $record = EmailCode::query()
            ->where('email', $email)
            ->latest()
            ->first();

        if (! $record || $record->isExpired()) {
            return false;
        }

        if (! password_verify($code, $record->code_hash)) {
            return false;
        }

        $record->delete();

        return true;
    }
}
