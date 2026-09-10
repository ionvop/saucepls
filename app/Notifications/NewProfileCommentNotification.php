<?php

namespace App\Notifications;

use App\Models\UserComment;
use Illuminate\Notifications\Notification;

/**
 * Sent to the owner of a profile when someone comments on it, and to the
 * author of a top-level comment when someone replies to it.
 */
class NewProfileCommentNotification extends Notification
{
    public function __construct(public UserComment $comment)
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
        return 'new_profile_comment';
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
            'profile_user_id' => $this->comment->profile_user_id,
            'profile_username' => $this->comment->profileUser?->username,
            'comment_id' => $this->comment->id,
            'parent_id' => $this->comment->parent_id,
            'message' => $this->comment->parent_id === null
                ? 'commented on your profile.'
                : 'replied to your comment.',
        ];
    }
}