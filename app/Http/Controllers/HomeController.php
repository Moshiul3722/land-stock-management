<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * @var DashboardService
     */
    protected $dashboardService;

    /**
     * Create a new controller instance.
     *
     * @param DashboardService $dashboardService
     * @return void
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->middleware('auth');
        $this->dashboardService = $dashboardService;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // A General User (role 5) gets a dashboard about their own requisitions and
        // notifications.
        if ((int) auth()->user()->role_id === 5) {
            return view('home_user', $this->ownDashboardData(auth()->user()));
        }

        // Every other role gets the same dashboard for the whole organisation: the
        // requisitions of all departments, plus how many users, product categories and
        // products there are. The notifications are still the signed-in user's own.
        $dashboardData = $this->dashboardService->getDashboardData();

        return view('home_user', $this->ownDashboardData(auth()->user(), true) + [
            'orgCards' => [
                'users' => (int) $dashboardData['userCount'],
                'categories' => (int) $dashboardData['categoryCount'],
                'products' => (int) $dashboardData['productCount'],
            ],
        ]);
    }

    /**
     * Figures for the signed-in user's own dashboard: how many requisitions they made, how
     * many were supplied and how many are waiting, their requisitions per month for the last
     * twelve months, and their five newest notifications. With $everyone the requisition
     * figures cover all users' requisitions instead (the notifications stay the user's own).
     */
    private function ownDashboardData(\App\Models\User $user, bool $everyone = false): array
    {
        $own = fn () => \App\Models\Requisition::query()
            ->when(! $everyone, fn ($query) => $query->where('requested_by_user_id', $user->id));

        $byStatus = $own()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        // One bar per month, oldest first, ending with the current month.
        $firstMonth = now()->startOfMonth()->subMonths(11);
        $perMonth = $own()
            ->where('requisition_date', '>=', $firstMonth)
            ->get(['requisition_date'])
            ->countBy(fn ($requisition) => \Illuminate\Support\Carbon::parse($requisition->requisition_date)->format('Y-m'));
        $months = collect(range(0, 11))->map(function (int $offset) use ($firstMonth, $perMonth) {
            $month = $firstMonth->copy()->addMonths($offset);

            return ['month' => $month, 'total' => (int) ($perMonth[$month->format('Y-m')] ?? 0)];
        });

        return [
            'user' => $user,
            'totalRequisitions' => (int) $byStatus->sum(),
            // "Supplied": handed over (delivered) and the ones closed after that (completed)
            'deliveredRequisitions' => (int) (($byStatus['delivered'] ?? 0) + ($byStatus['completed'] ?? 0)),
            'pendingRequisitions' => (int) ($byStatus['pending'] ?? 0),
            'months' => $months,
            'latestNotifications' => \App\Models\Notification::query()
                ->where('user_to_id', $user->id)
                ->with('userFrom:id,name,name_bn')
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
        ];
    }
}

