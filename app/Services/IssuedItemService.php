<?php

namespace App\Services;

use App\Repositories\IssuedItemRepository;
use App\Models\User;

class IssuedItemService
{
    protected $issuedItemRepository;

    public function __construct(IssuedItemRepository $issuedItemRepository)
    {
        $this->issuedItemRepository = $issuedItemRepository;
    }

    /**
     * Fetch the personal asset ledger for a user.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMyIssuedItems(User $user)
    {
        return $this->issuedItemRepository->getAllForUser($user->id);
    }
}