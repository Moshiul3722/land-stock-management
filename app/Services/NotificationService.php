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

    /**
     * Get all notifications for a user (paginated).
     */
    public function getAllForUserPaginated($userId, $perPage = 10)
    {
        return $this->notificationRepository->model()::where('user_to_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllAsReadForUser($userId)
    {
        return $this->notificationRepository->model()::where('user_to_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
    }

    /** Roles that see everyone's notifications (Super Admin, Admin); the rest see only their own. */
    public const SEE_ALL_ROLES = [1, 2];

    public function seesAll($user): bool
    {
        return $user && in_array((int) $user->role_id, self::SEE_ALL_ROLES, true);
    }

    /** Notifications the user may see: everyone's for Super Admin / Admin, else their own. */
    public function visibleQuery($user)
    {
        return \App\Models\Notification::query()
            ->when(! $this->seesAll($user), fn ($q) => $q->where('user_to_id', $user?->id ?? 0));
    }

    /**
     * The bell's list: a user's own unread notifications; Super Admin / Admin get the latest
     * notifications of everyone (read or not), newest first.
     */
    public function getRecentForBell($user, $limit = 5)
    {
        if (! $this->seesAll($user)) {
            return $this->getRecentUnread($user?->id, $limit);
        }

        return $this->visibleQuery($user)
            ->with('userTo:id,name,name_bn')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /** The notifications page: what the user may see, newest first, with the recipient. */
    public function getVisiblePaginated($user, $perPage = 10)
    {
        return $this->visibleQuery($user)
            ->with(['userFrom:id,name,name_bn', 'userTo:id,name,name_bn'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
