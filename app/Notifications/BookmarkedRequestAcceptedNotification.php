<?php

namespace App\Notifications;

use App\Models\SauceAnswer;
use App\Models\SauceRequest;
use Illuminate\Notifications\Notification;

/**
 * Sent to every user who bookmarked a sauce request when that request has an
 * answer accepted as the correct one.
 */
class BookmarkedRequestAcceptedNotification extends Notification
{
    public function __construct(public SauceRequest $sauceRequest, public SauceAnswer $answer)
    {
    }

    /**
     * Deliver via the database channel only.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * The short type stored in the notifications table.
     */
    public function databaseType(object $notifiable): string
    {
        return 'bookmarked_request_accepted';
    }

    /**
     * The data stored in the notifications table.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'actor_id' => $this->sauceRequest->user_id,
            'actor_username' => $this->sauceRequest->user?->username,
            'sauce_request_id' => $this->sauceRequest->id,
            'sauce_request_title' => $this->sauceRequest->title,
            'answer_id' => $this->answer->id,
            'message' => 'accepted an answer on a sauce request you bookmarked.',
        ];
    }
}