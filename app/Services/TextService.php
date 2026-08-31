<?php

namespace App\Services;

use App\Models\SauceRequest;
use App\Models\SauceRequestTextHistory;
use App\Models\User;

/**
 * Updates the extracted text on sauce requests while recording each change
 * in the text history for attribution. Each history row stores a full
 * snapshot of the resulting text (no diff analysis).
 */
class TextService
{
    /**
     * Replace the extracted text on a sauce request and record the change
     * in the history. No-op if the text is unchanged.
     */
    public function update(SauceRequest $sauceRequest, string $text, User $user): void
    {
        $text = trim($text);

        if ($text === $sauceRequest->text) {
            return;
        }

        $sauceRequest->update(['text' => $text]);

        $this->record($sauceRequest, $user);
    }

    /**
     * Restore a sauce request's extracted text to the state it was in
     * immediately after the given history entry was applied. Each history
     * row stores a full snapshot of the resulting text, so the target
     * state is read directly from it. The resulting change is recorded in
     * the history for attribution.
     */
    public function restoreTo(SauceRequest $sauceRequest, SauceRequestTextHistory $target, User $user): void
    {
        $this->update($sauceRequest, (string) $target->text_snapshot, $user);
    }

    /**
     * Persist a text change for attribution.
     */
    private function record(SauceRequest $sauceRequest, User $user): void
    {
        SauceRequestTextHistory::create([
            'sauce_request_id' => $sauceRequest->id,
            'user_id' => $user->id,
            'text_snapshot' => $sauceRequest->text,
        ]);
    }
}