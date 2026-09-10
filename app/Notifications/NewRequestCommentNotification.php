<?php

namespace App\Notifications;

use App\Models\SauceRequestComment;
use Illuminate\Notifications\Notification;

/**
 * Sent to the author of a sauce request when someone comments on it, and to
 * the author of a top-level comment when someone replies to it.
 */
class NewRequestCommentNotification extends Notification
{
    public function __construct(public SauceRequestComment $comment)
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
        return 'new_request_comment';
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
            'sauce_request_id' => $this->comment->sauce_request_id,
            'sauce_request_title' => $this->comment->sauceRequest?->title,
            'comment_id' => $this->comment->id,
            'parent_id' => $this->comment->parent_id,
            'message' => $this->comment->parent_id === null
                ? 'commented on your sauce request.'
                : 'replied to your comment.',
        ];
    }
}