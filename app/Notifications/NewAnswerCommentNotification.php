<?php

namespace App\Notifications;

use App\Models\SauceAnswerComment;
use Illuminate\Notifications\Notification;

/**
 * Sent to the author of a sauce answer when someone comments on it, and to
 * the author of the sauce request the answer belongs to.
 */
class NewAnswerCommentNotification extends Notification
{
    public function __construct(public SauceAnswerComment $comment)
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
        return 'new_answer_comment';
    }

    /**
     * The data stored in the notifications table.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'actor_id' => $this->comment->user_id,
            'actor_username' => $this->comment->user?->username,
            'sauce_request_id' => $this->comment->sauceAnswer?->sauce_request_id,
            'sauce_request_title' => $this->comment->sauceAnswer?->sauceRequest?->title,
            'answer_id' => $this->comment->sauce_answer_id,
            'comment_id' => $this->comment->id,
            'message' => 'commented on your answer.',
        ];
    }
}