<?php

namespace App\Http\Controllers;

use user;
use NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AppBaseController;

class NotificationController extends AppBaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getUnreadNotifications(Request $request)
    {
        $userTo = $request->input('user_to');
        $unreadNotifications = $this->notificationService->GetUnread($userTo);
        return response()->json($unreadNotifications);
    }

    public function markAllAsRead(Request $request)
    {
        $userTo = $request->input('user_to');
        $markAllAsRead = $this->notificationService->MarkAllAsRead($userTo);
        return response()->json(['message' => 'All notifications marked as read']);
    }

    public function viewAllNotifications(Request $request)
    {
        $userTo = $request->input('user_to');
        $allNotifications = $this->notificationService->ViewAll($userTo);
        return response()->json($allNotifications);
    }

    public function sendNotification(Request $request)
    {
        if (Auth::check()) {
            $userFrom = Auth::user()->id;
        } else {
            return response()->json(['message' => 'No authenticated user found'], 401);
        }
    
        $userTo = $request->input('user_to');
        $currentRoute = $request->route();
        $url = url()->current();
        $notificationText = $request->input('notification_text');
        
        $detailsEntityId = $this->notificationService->SendNotification($userFrom, $userTo, $url, $notificationText);
    
        return response()->json(['notification_id' => $detailsEntityId]);
    }
}