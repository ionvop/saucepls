<?php

namespace App\Notifications;

use App\Models\SauceAnswer;
use Illuminate\Notifications\Notification;

/**
 * Sent to the author of a sauce request when someone provides an answer.
 */
class NewAnswerNotification extends Notification
{
    public function __construct(public SauceAnswer $answer)
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
        return 'new_answer';
    }

    /**
     * The data stored in the notifications table.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'actor_id' => $this->answer->user_id,
            'actor_username' => $this->answer->user?->username,
            'sauce_request_id' => $this->answer->sauce_request_id,
            'sauce_request_title' => $this->answer->sauceRequest?->title,
            'answer_id' => $this->answer->id,
            'message' => 'provided an answer to your sauce request.',
        ];
    }
}