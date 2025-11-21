<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\BaseRepository;

class NotificationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'user_from_id',
        'user_to_id',
        'is_read',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Notification::class;
    }

    /**
     * Get unread notifications for a specific user.
     */
    public function getUnreadForUser($userId)
    {
        return $this->model->where('user_to_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
