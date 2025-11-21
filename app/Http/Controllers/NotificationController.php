<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the list of all notifications.
     */
    public function index()
    {
        $notifications = $this->notificationService->getAllForUserPaginated(Auth::id());
        return view('notification.index', compact('notifications'));
    }

    /**
     * Mark a single notification as read and redirect.
     */
    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);
        
        // If there is a URL associated with the notification, redirect to it
        $notification = \App\Models\Notification::find($id);
        if ($notification && $notification->url) {
            return redirect($notification->url);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllRead()
    {
        $this->notificationService->markAllAsReadForUser(Auth::id());
        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}