<?php

use Illuminate\Support\Facades\DB;

class NotificationService
{
    public $userTo;
    public $userFrom;
    public $notification;
    public $isViewed;
    public $url;
    public $notificationText;

    public function GetUnread($userTo)
    {
        $this->userTo = $userTo;
        $this->isViewed = '0';

        $unreadNotifications = DB::select('SELECT * FROM notifications WHERE to_user = ? AND is_viewed = ? ORDER BY created_at DESC', [$this->userTo, $this->isViewed]);
        return $unreadNotifications;
    }

    public function MarkAllAsRead($userTo)
    {
        $this->userTo = $userTo;
        $markAllAsRead = DB::update('UPDATE notifications SET is_viewed = ? WHERE to_user = ?', ['1', $this->userTo]);
        return $markAllAsRead;
    }

    public function ViewAll($userTo)
    {
        $this->userTo = $userTo;
        $allNotifications = DB::select('SELECT * FROM notifications WHERE to_user = ? ORDER BY created_at DESC', [$this->userTo]);
        return $allNotifications;
    }

    public function SendNotification($userFrom, $userTo, $url, $notificationText)
    {
        $this->userTo = $userTo;
        $this->userFrom = $userFrom;
        $this->url = $url;
        $this->notificationText = $notificationText;

        $detailsEntityId = DB::table('notifications')->insertGetId([
            'notification' => $this->notificationText,
            'to_user' => $this->userTo,
            'from_user' => $this->userFrom,
            'url' => $this->url
        ]);

        return $detailsEntityId;
    }
}
