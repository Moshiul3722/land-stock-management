<?php

namespace App\Http\Controllers;

use App\Support\LowStock;
use Illuminate\Support\Facades\Auth;

/**
 * The low-stock list on A4 paper. ?preview=1 shows it with a print button; otherwise the
 * print dialog opens at once. Only for the roles that see the low-stock line.
 */
class LowStockController extends Controller
{
    public function printList()
    {
        abort_unless(LowStock::canSee(Auth::user()), 403);

        return view('low-stock.print', ['items' => LowStock::items()]);
    }
}
