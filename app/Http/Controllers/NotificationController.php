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
        // A General User (role 5) can open only notifications addressed to them: someone
        // else's is neither marked as read nor followed.
        if ((int) Auth::user()->role_id === 5) {
            $owned = \App\Models\Notification::whereKey($id)->where('user_to_id', Auth::id())->exists();
            abort_unless($owned, 403);
        }

        $this->notificationService->markAsRead($id);
        
        // If there is a URL associated with the notification, redirect to it
        $notification = \App\Models\Notification::find($id);
        if ($notification && $notification->url) {
            return redirect($this->withHighlight($notification->url, (string) $notification->notification_text));
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * A notification about a requisition ("... Requisition #169 ...") that leads to a requisition
     * list gets ?highlight=169, so that list can show the row with a background colour.
     * Any other link is returned as it is.
     */
    private function withHighlight(string $url, string $text): string
    {
        $path = rtrim((string) parse_url($url, PHP_URL_PATH), '/');
        $isRequisitionList = str_ends_with($path, '/requisitions') || str_ends_with($path, '/requisitions/pending');

        if (! $isRequisitionList || ! preg_match('/Requisition #(\d+)/', $text, $match)) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . 'highlight=' . $match[1];
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