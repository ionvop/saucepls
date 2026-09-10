<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Show the authenticated user's notifications. Viewing the page marks
     * all of the user's notifications as read.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $notifications = $user->notifications()->paginate(20);

        // Mark all notifications as read once the page is viewed.
        $user->unreadNotifications()->update(['read_at' => now()]);

        return view('pages.notifications', [
            'notifications' => $notifications,
        ]);
    }
}