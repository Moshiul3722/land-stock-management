<?php

namespace App\Http\Controllers;

use App\Services\IssuedItemService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IssuedItemController extends Controller
{
    protected $issuedItemService;

    public function __construct(IssuedItemService $issuedItemService)
    {
        $this->issuedItemService = $issuedItemService;
    }

    /**
     * Display the list of items issued to the current user.
     */
    public function index()
    {
        $items = $this->issuedItemService->getMyIssuedItems(Auth::user());
        
        return view('issued_item.index', compact('items'));
    }
}