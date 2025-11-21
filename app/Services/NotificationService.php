<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use Exception;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    protected $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Create a new notification.
     *
     * @param int $toUserId
     * @param string $text
     * @param string|null $url
     * @param int|null $fromUserId
     * @return \App\Models\Notification|null
     */
    public function createNotification($toUserId, $text, $url = null, $fromUserId = null)
    {
        try {
            return $this->notificationRepository->create([
                'user_to_id' => $toUserId,
                'user_from_id' => $fromUserId,
                'notification_text' => $text,
                'url' => $url,
                'is_read' => false,
            ]);
        } catch (Exception $e) {
            Log::error("Failed to create notification: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Mark a notification as read.
     *
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead($notificationId)
    {
        try {
            $notification = $this->notificationRepository->find($notificationId);
            if ($notification) {
                $this->notificationRepository->update([
                    'is_read' => true,
                    'read_at' => now(),
                ], $notificationId);
                return true;
            }
            return false;
        } catch (Exception $e) {
            Log::error("Failed to mark notification as read: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get unread notifications count for a user.
     * * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId)
    {
        return $this->notificationRepository->getUnreadForUser($userId)->count();
    }
    
    /**
     * Get recent unread notifications for display in navbar.
     */
    public function getRecentUnread($userId, $limit = 5)
    {
        return $this->notificationRepository->model()::where('user_to_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}