{{--
    Dashboard of a General User (role 5): their own requisitions and notifications.
    Data comes from HomeController::ownDashboardData(). Other roles see home.blade.php.
    The chart is drawn with plain HTML/CSS (no chart library).
--}}
@extends('layouts.app')

@push('page_css')
    <style>
        .udash { padding: 1rem 0.5rem 1.5rem; }
        .udash .ud-card { border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 12px 28px -18px rgba(15, 23, 42, 0.25); }

        /* ---- Greeting ---- */
        .udash .ud-hero { position: relative; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; overflow: hidden; margin-bottom: 1rem; padding: 1.4rem 1.5rem; border-radius: 1.1rem; background: linear-gradient(120deg, #13244f 0%, #1d4fd8 62%, #4f46e5 100%); color: #fff; box-shadow: 0 18px 36px -22px rgba(29, 79, 216, 0.8); }
        .udash .ud-hero::before, .udash .ud-hero::after { content: ""; position: absolute; border: 1.5px dashed rgba(255, 255, 255, 0.22); border-radius: 50%; pointer-events: none; }
        .udash .ud-hero::before { top: -5rem; right: -3rem; width: 14rem; height: 14rem; }
        .udash .ud-hero::after { top: -8rem; right: -6rem; width: 20rem; height: 20rem; border-color: rgba(255, 255, 255, 0.13); }
        .udash .ud-hero > * { position: relative; z-index: 1; }
        .udash .ud-hello { margin: 0; color: rgba(226, 232, 240, 0.9); font-size: 0.9rem; }
        .udash .ud-name { margin: 0.1rem 0 0.25rem; color: #fff; font-size: 1.55rem; font-weight: 700; line-height: 1.25; overflow-wrap: anywhere; }
        .udash .ud-meta { display: flex; flex-wrap: wrap; gap: 0.35rem 1rem; margin: 0; color: rgba(226, 232, 240, 0.9); font-size: 0.875rem; }
        .udash .ud-meta span { display: inline-flex; align-items: center; gap: 0.4rem; }
        .udash .ud-cta { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; min-height: 2.75rem; padding: 0 1.2rem; border-radius: 0.75rem; background: #fff; color: #1d4fd8; font-size: 0.95rem; font-weight: 700; white-space: nowrap; box-shadow: 0 10px 22px -12px rgba(2, 6, 23, 0.6); transition: transform 0.12s, box-shadow 0.15s; }
        .udash .ud-cta:hover { color: #1a3f9f; text-decoration: none; box-shadow: 0 14px 26px -12px rgba(2, 6, 23, 0.7); transform: translateY(-1px); }
        .udash .ud-cta:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.45); }

        /* ---- The three figures: a titled band on top, the number below ---- */
        .udash .ud-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 1rem; }
        .udash .ud-stat { display: flex; flex-direction: column; min-height: 11.5rem; overflow: hidden; color: inherit; transition: transform 0.12s, box-shadow 0.15s; }
        .udash .ud-stat:hover { color: inherit; text-decoration: none; transform: translateY(-2px); box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 18px 32px -18px rgba(15, 23, 42, 0.35); }
        .udash a.ud-stat:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.3); }
        .udash .ud-stat-title { display: flex; align-items: center; gap: 0.6rem; margin: 0; padding: 0.8rem 1.25rem; background: var(--ud-band); color: #fff; font-size: 1.15rem; font-weight: 700; line-height: 1.3; }
        .udash .ud-stat-title i { font-size: 1rem; opacity: 0.9; }
        .udash .ud-stat-body { display: flex; flex: 1 1 auto; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem 1.1rem; }
        .udash .ud-stat-value { display: block; color: #0f172a; font-size: 3rem; font-weight: 700; line-height: 1.05; font-variant-numeric: tabular-nums; }
        .udash .ud-stat-note { display: block; margin-top: 0.35rem; color: #64748b; font-size: 0.9rem; }
        .udash .ud-stat-icon { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; width: 4.25rem; height: 4.25rem; border-radius: 1.2rem; background: var(--ud-soft); color: var(--ud-tone); font-size: 1.7rem; }
        .udash .ud-stat.is-all { --ud-tone: #1d65db; --ud-soft: #eef4ff; --ud-band: linear-gradient(120deg, #1d4fd8, #4f46e5); }
        .udash .ud-stat.is-done { --ud-tone: #059669; --ud-soft: #ecfdf5; --ud-band: linear-gradient(120deg, #047857, #10b981); }
        .udash .ud-stat.is-users { --ud-tone: #7c3aed; --ud-soft: #f5f3ff; --ud-band: linear-gradient(120deg, #6d28d9, #a855f7); }
        .udash .ud-stat.is-cats { --ud-tone: #e11d48; --ud-soft: #fff1f2; --ud-band: linear-gradient(120deg, #be123c, #fb7185); }
        .udash .ud-stat.is-prods { --ud-tone: #0891b2; --ud-soft: #ecfeff; --ud-band: linear-gradient(120deg, #0e7490, #22d3ee); }
        .udash .ud-stat.is-wait { --ud-tone: #d97706; --ud-soft: #fffbeb; --ud-band: linear-gradient(120deg, #b45309, #f59e0b); }

        /* ---- Section cards (notifications, chart): a header and a body, full width ---- */
        .udash .ud-section { overflow: hidden; margin-bottom: 1rem; }
        .udash .ud-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.6rem 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid #eef2f7; }
        .udash .ud-head-main { display: flex; align-items: center; gap: 0.8rem; min-width: 0; }
        .udash .ud-head-icon { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; width: 2.6rem; height: 2.6rem; border-radius: 0.8rem; background: #eef4ff; color: #1d65db; font-size: 1.05rem; }
        .udash .ud-title { margin: 0; color: #0f172a; font-size: 1.15rem; font-weight: 700; line-height: 1.3; }
        .udash .ud-sub { margin: 0; color: #64748b; font-size: 0.85rem; }
        .udash .ud-chip { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.2rem 0.65rem; border-radius: 999px; background: #eef4ff; color: #1d4fd8; font-size: 0.78rem; font-weight: 700; white-space: nowrap; }
        .udash .ud-chip::before { content: ""; width: 0.4rem; height: 0.4rem; border-radius: 50%; background: currentColor; }
        .udash .ud-more { display: inline-flex; align-items: center; gap: 0.45rem; min-height: 2.4rem; padding: 0 0.95rem; border: 1px solid #c7d9fb; border-radius: 0.65rem; background: #f5f8ff; color: #1d4fd8; font-size: 0.875rem; font-weight: 700; white-space: nowrap; transition: background-color 0.15s, border-color 0.15s; }
        .udash .ud-more:hover { border-color: #1d65db; background: #eef4ff; color: #1a3f9f; text-decoration: none; }
        .udash .ud-more:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.3); }

        /* ---- Notifications: one tile each ---- */
        .udash .ud-notes { display: grid; gap: 0.6rem; margin: 0; padding: 1rem 1.25rem 1.25rem; list-style: none; }
        .udash .ud-note a { display: flex; align-items: center; gap: 0.9rem; padding: 0.8rem 1rem; border: 1px solid #e2e8f0; border-radius: 0.85rem; background: #fff; color: inherit; transition: background-color 0.15s, border-color 0.15s, transform 0.12s, box-shadow 0.15s; }
        .udash .ud-note a:hover { border-color: #c7d9fb; background: #f8fafc; color: inherit; text-decoration: none; transform: translateY(-1px); box-shadow: 0 10px 20px -16px rgba(15, 23, 42, 0.45); }
        .udash .ud-note a:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.3); }
        .udash .ud-note.is-unread a { border-color: #c7d9fb; background: #f5f8ff; }
        .udash .ud-note-icon { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; width: 2.75rem; height: 2.75rem; border-radius: 0.85rem; font-size: 1.1rem; }
        .udash .ud-note-icon.is-success { background: #ecfdf5; color: #059669; }
        .udash .ud-note-icon.is-info { background: #eef4ff; color: #1d65db; }
        .udash .ud-note-icon.is-danger { background: #fff1f2; color: #e11d48; }
        .udash .ud-note-icon.is-warning { background: #fffbeb; color: #d97706; }
        .udash .ud-note-icon.is-neutral { background: #f1f5f9; color: #475569; }
        .udash .ud-note-main { flex: 1 1 auto; min-width: 0; }
        .udash .ud-note-text { margin: 0; color: #334155; font-size: 0.95rem; line-height: 1.45; overflow-wrap: anywhere; }
        .udash .ud-note.is-unread .ud-note-text { color: #0f172a; font-weight: 700; }
        .udash .ud-note-from { display: inline-flex; align-items: center; gap: 0.35rem; margin-top: 0.15rem; color: #64748b; font-size: 0.8125rem; }
        .udash .ud-note-from i { color: #94a3b8; font-size: 0.75rem; }
        .udash .ud-note-side { display: flex; flex: 0 0 auto; flex-direction: column; align-items: flex-end; gap: 0.25rem; color: #64748b; font-size: 0.8125rem; text-align: right; white-space: nowrap; }
        .udash .ud-note-side strong { color: #334155; font-weight: 600; }
        .udash .ud-note-new { padding: 0.12rem 0.55rem; border-radius: 999px; background: #1d65db; color: #fff; font-size: 0.6875rem; font-weight: 700; }
        .udash .ud-note-go { flex: 0 0 auto; color: #cbd5e1; font-size: 0.8rem; }
        .udash .ud-empty { display: flex; flex-direction: column; align-items: center; gap: 0.6rem; padding: 2.5rem 1.25rem; color: #64748b; font-size: 0.95rem; text-align: center; }
        .udash .ud-empty i { display: inline-flex; align-items: center; justify-content: center; width: 3.5rem; height: 3.5rem; border-radius: 50%; background: #f1f5f9; color: #94a3b8; font-size: 1.3rem; }

        /* ---- Chart ---- */
        .udash .ud-chart-box { position: relative; height: 22rem; padding: 1rem 1.25rem 1.1rem; }
        .udash .ud-chart-box canvas { display: none; width: 100% !important; height: 100% !important; }
        .udash .ud-chart-box.is-ready canvas { display: block; }
        .udash .ud-chart-box.is-ready .ud-plain { display: none; }
        /* The same figures as plain bars: shown until the chart is drawn, and if it cannot be */
        .udash .ud-plain { display: flex; flex-direction: column; height: 100%; }
        .udash .ud-chart { display: grid; flex: 1 1 auto; grid-template-columns: repeat(6, minmax(0, 1fr)); align-items: end; gap: 0.5rem; min-height: 0; margin: 0; padding: 0; list-style: none; }
        .udash .ud-bar-col { display: flex; flex-direction: column; align-items: center; justify-content: flex-end; height: 100%; min-width: 0; }
        .udash .ud-bar-value { margin-bottom: 0.25rem; color: #334155; font-size: 0.8rem; font-weight: 700; font-variant-numeric: tabular-nums; }
        .udash .ud-bar { width: 100%; max-width: 6.5rem; min-height: 4px; border-radius: 0.5rem 0.5rem 0.2rem 0.2rem; background: linear-gradient(180deg, #4f8ff7, #1d4fd8); }
        .udash .ud-bar.is-empty { background: #e2e8f0; }
        .udash .ud-bar.is-now { background: linear-gradient(180deg, #818cf8, #4f46e5); }
        .udash .ud-months { display: grid; flex: 0 0 auto; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 0.5rem; margin: 0; padding: 0.5rem 0 0; border-top: 1px solid #eef2f7; list-style: none; color: #64748b; font-size: 0.75rem; text-align: center; }
        .udash .ud-months li { min-width: 0; overflow: hidden; line-height: 1.25; }
        .udash .ud-months .is-now { color: #4f46e5; font-weight: 700; }
        .udash .ud-months small { display: block; color: #94a3b8; font-size: 0.7rem; }

        @media (max-width: 991.98px) {
            .udash .ud-stats { grid-template-columns: minmax(0, 1fr); }
            .udash .ud-stat { min-height: 0; }
        }
        @media (max-width: 767.98px) {
            .udash .ud-cta { width: 100%; }
            .udash .ud-head, .udash .ud-notes, .udash .ud-chart-box { padding-right: 1rem; padding-left: 1rem; }
            .udash .ud-more { width: 100%; justify-content: center; }
            .udash .ud-note a { flex-wrap: wrap; align-items: flex-start; padding: 0.75rem 0.85rem; }
            .udash .ud-note-main { flex-basis: calc(100% - 3.75rem); }
            .udash .ud-note-side { flex: 1 1 100%; flex-direction: row; align-items: center; flex-wrap: wrap; justify-content: flex-start; gap: 0.2rem 0.6rem; padding-left: 3.65rem; text-align: left; }
            .udash .ud-note-go { display: none; }
            .udash .ud-chart-box { height: 18rem; }
            .udash .ud-chart, .udash .ud-months { gap: 0.25rem; }
            .udash .ud-bar-value { font-size: 0.7rem; }
            .udash .ud-months { font-size: 0.65rem; }
            .udash .ud-months small { display: none; }
        }
        @media (prefers-reduced-motion: reduce) { .udash * { transition: none !important; } }
    </style>
@endpush

@section('content')
    @php
        $isBn = app()->getLocale() === 'bn';
        $digits = fn ($value) => $isBn
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;
        $t = $isBn ? [
            'hello' => 'স্বাগতম',
            'new' => 'নতুন রিকুইজিশন',
            'all' => 'আমার মোট রিকুইজিশন',
            'all_note' => 'এ পর্যন্ত করা সব রিকুইজিশন',
            'done' => 'সরবরাহকৃত রিকুইজিশন',
            'done_note' => 'মালামাল বুঝে পাওয়া গেছে',
            'wait' => 'অপেক্ষমাণ রিকুইজিশন',
            'wait_note' => 'অনুমোদন বা বিতরণের অপেক্ষায়',
            'chart' => 'মাস অনুযায়ী আমার রিকুইজিশন',
            'chart_sub' => 'সর্বশেষ ৬ মাস',
            'chart_total' => 'মোট :count টি',
            'notes' => 'সর্বশেষ নোটিফিকেশন',
            'notes_all' => 'সব নোটিফিকেশন দেখুন',
            'notes_sub' => 'আপনার সবচেয়ে নতুন ৫টি নোটিফিকেশন',
            'notes_new' => ':countটি নতুন',
            'new_badge' => 'নতুন',
            'tooltip' => ':countটি রিকুইজিশন',
            'axis' => 'রিকুইজিশনের সংখ্যা',
            'notes_empty' => 'এখনো কোনো নোটিফিকেশন নেই।',
            'see_list' => 'রিকুইজিশন তালিকা',
            'bar' => ':month: :count টি রিকুইজিশন',
        ] : [
            'hello' => 'Welcome',
            'new' => 'New requisition',
            'all' => 'My requisitions',
            'all_note' => 'Every requisition made so far',
            'done' => 'Supplied requisitions',
            'done_note' => 'Goods have been received',
            'wait' => 'Pending requisitions',
            'wait_note' => 'Waiting for approval or delivery',
            'chart' => 'My requisitions by month',
            'chart_sub' => 'Last 6 months',
            'chart_total' => ':count in total',
            'notes' => 'Latest notifications',
            'notes_all' => 'See all notifications',
            'notes_sub' => 'Your 5 newest notifications',
            'notes_new' => ':count new',
            'new_badge' => 'NEW',
            'tooltip' => ':count requisitions',
            'axis' => 'Number of requisitions',
            'notes_empty' => 'No notifications yet.',
            'see_list' => 'Requisition list',
            'bar' => ':month: :count requisitions',
        ];
        // The organisation-wide dashboard (every role except General User): three more cards,
        // and the requisition figures are about all departments, not the user's own.
        $orgCards = $orgCards ?? null;
        if ($orgCards) {
            $t = array_merge($t, $isBn ? [
                'all' => 'মোট রিকুইজিশন',
                'all_note' => 'সব ডিপার্টমেন্টের সব রিকুইজিশন',
                'done_note' => 'মালামাল বিতরণ করা হয়েছে',
                'chart' => 'মাস অনুযায়ী রিকুইজিশন',
                'users' => 'মোট ব্যবহারকারী',
                'users_note' => 'সিস্টেমের সব ব্যবহারকারী',
                'cats' => 'প্রোডাক্ট ক্যাটাগরি',
                'cats_note' => 'প্রোডাক্টের সব ক্যাটাগরি',
                'prods' => 'মোট প্রোডাক্ট',
                'prods_note' => 'তালিকাভুক্ত সব প্রোডাক্ট',
            ] : [
                'all' => 'Total requisitions',
                'all_note' => 'Every requisition of all departments',
                'done_note' => 'Goods have been handed over',
                'chart' => 'Requisitions by month',
                'users' => 'Total users',
                'users_note' => 'All users of the system',
                'cats' => 'Product categories',
                'cats_note' => 'All product categories',
                'prods' => 'Total products',
                'prods_note' => 'All listed products',
            ]);
        }
        $name = $isBn && trim((string) $user->name_bn) !== '' ? $user->name_bn : $user->name;
        $department = $user->department;
        $departmentName = $department ? ($isBn ? ($department->name_bn ?: $department->name) : $department->name) : '';
        $today = $digits(now()->locale($isBn ? 'bn' : 'en')->translatedFormat($isBn ? 'l, j F Y' : 'l, d M Y'));
        // Today in the Bangla calendar (revised 2019 calendar), e.g. ২৯ কার্তিক, ১৪৩৩ বঙ্গাব্দ
        $bongabdo = (function () use ($digits, $isBn) {
            $date = now()->startOfDay();
            $year = $date->year;
            $start = \Illuminate\Support\Carbon::create($year, 4, 14)->startOfDay();
            if ($date->lt($start)) {
                $year -= 1;
                $start = \Illuminate\Support\Carbon::create($year, 4, 14)->startOfDay();
            }
            // Falgun falls in February / March of the next Gregorian year
            $leap = \Illuminate\Support\Carbon::create($year + 1, 1, 1)->isLeapYear();
            $lengths = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, $leap ? 30 : 29, 30];
            $names = $isBn
                ? ['বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র']
                : ['Boishakh', 'Joishtho', 'Asharh', 'Srabon', 'Bhadro', 'Ashwin', 'Kartik', 'Ogrohayon', 'Poush', 'Magh', 'Falgun', 'Choitro'];
            $day = (int) round($start->diffInDays($date));
            $month = 0;
            while ($month < 11 && $day >= $lengths[$month]) {
                $day -= $lengths[$month];
                $month++;
            }

            return $digits($day + 1) . ' ' . $names[$month] . ', ' . $digits($year - 593) . ($isBn ? ' বঙ্গাব্দ' : ' BS');
        })();
        $canCreate = app(\App\Services\MenuPermissionService::class)->allows($user, 'requisitions.create');
        // Where "requisition list" leads: a General User's own list; for the other roles the
        // list their role may open (none if the role has neither)
        $listUrl = route('requisitions.index');
        if ($orgCards) {
            $listRoute = collect(['requisitions.index', 'requisitions.pending'])
                ->first(fn ($routeName) => app(\App\Services\MenuPermissionService::class)->allows($user, $routeName));
            $listUrl = $listRoute ? route($listRoute) : null;
        }
        // Bars: the tallest month fills the chart's height
        // The chart shows the last six months only
        $months = $months->slice(-6)->values();
        $peak = max(1, (int) $months->max('total'));
        $chartTotal = (int) $months->sum('total');
        // Icon and colour by what the notification is about (same as the notifications page)
        $kind = function (string $text) {
            $lower = strtolower($text);
            return match (true) {
                str_contains($lower, 'reject') => ['fa-times-circle', 'is-danger'],
                str_contains($lower, 'deliver') => ['fa-truck', 'is-success'],
                str_contains($lower, 'approved') => ['fa-check-circle', 'is-info'],
                str_contains($lower, 'pending') => ['fa-hourglass-half', 'is-warning'],
                default => ['fa-bell', 'is-neutral'],
            };
        };
    @endphp

    <section class="content udash">
        <div class="container-fluid">

            {{-- ============ Greeting ============ --}}
            <div class="ud-hero">
                <div>
                    <p class="ud-hello">{{ $t['hello'] }}</p>
                    <h1 class="ud-name">{{ $name }}</h1>
                    <p class="ud-meta">
                        @if ($departmentName !== '')
                            <span><i class="fas fa-building" aria-hidden="true"></i>{{ $departmentName }}</span>
                        @endif
                        <span><i class="fas fa-calendar-day" aria-hidden="true"></i>{{ $today }}</span>
                        <span><i class="fas fa-calendar-alt" aria-hidden="true"></i>{{ $bongabdo }}</span>
                    </p>
                </div>
                @if ($canCreate)
                    <a href="{{ route('requisitions.index', ['create' => 1]) }}" wire:navigate class="ud-cta">
                        <i class="fas fa-plus" aria-hidden="true"></i> {{ $t['new'] }}
                    </a>
                @endif
            </div>

            {{-- ============ My requisitions: all, supplied, waiting ============ --}}
            @if ($orgCards)
                <div class="ud-stats">
                    @foreach ([['users', 'is-users', 'fa-users'], ['cats', 'is-cats', 'fa-layer-group'], ['prods', 'is-prods', 'fa-boxes']] as [$key, $tone, $icon])
                        <div class="ud-card ud-stat {{ $tone }}">
                            <h2 class="ud-stat-title"><i class="fas {{ $icon }}" aria-hidden="true"></i>{{ $t[$key] }}</h2>
                            <span class="ud-stat-body">
                                <span>
                                    <span class="ud-stat-value">{{ $digits($orgCards[$key === 'cats' ? 'categories' : ($key === 'prods' ? 'products' : 'users')]) }}</span>
                                    <span class="ud-stat-note">{{ $t[$key . '_note'] }}</span>
                                </span>
                                <span class="ud-stat-icon" aria-hidden="true"><i class="fas {{ $icon }}"></i></span>
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="ud-stats">
                <a @if ($listUrl) href="{{ $listUrl }}" wire:navigate @endif class="ud-card ud-stat is-all">
                    <h2 class="ud-stat-title"><i class="fas fa-clipboard-list" aria-hidden="true"></i>{{ $t['all'] }}</h2>
                    <span class="ud-stat-body">
                        <span>
                            <span class="ud-stat-value">{{ $digits($totalRequisitions) }}</span>
                            <span class="ud-stat-note">{{ $t['all_note'] }}</span>
                        </span>
                        <span class="ud-stat-icon" aria-hidden="true"><i class="fas fa-clipboard-list"></i></span>
                    </span>
                </a>
                <div class="ud-card ud-stat is-done">
                    <h2 class="ud-stat-title"><i class="fas fa-truck" aria-hidden="true"></i>{{ $t['done'] }}</h2>
                    <span class="ud-stat-body">
                        <span>
                            <span class="ud-stat-value">{{ $digits($deliveredRequisitions) }}</span>
                            <span class="ud-stat-note">{{ $t['done_note'] }}</span>
                        </span>
                        <span class="ud-stat-icon" aria-hidden="true"><i class="fas fa-truck"></i></span>
                    </span>
                </div>
                <div class="ud-card ud-stat is-wait">
                    <h2 class="ud-stat-title"><i class="fas fa-hourglass-half" aria-hidden="true"></i>{{ $t['wait'] }}</h2>
                    <span class="ud-stat-body">
                        <span>
                            <span class="ud-stat-value">{{ $digits($pendingRequisitions) }}</span>
                            <span class="ud-stat-note">{{ $t['wait_note'] }}</span>
                        </span>
                        <span class="ud-stat-icon" aria-hidden="true"><i class="fas fa-hourglass-half"></i></span>
                    </span>
                </div>
            </div>

            {{-- ============ Five newest notifications (full width) ============ --}}
            @php $unreadShown = $latestNotifications->where('is_read', false)->count(); @endphp
            <div class="ud-card ud-section">
                <div class="ud-head">
                    <div class="ud-head-main">
                        <span class="ud-head-icon" aria-hidden="true"><i class="fas fa-bell"></i></span>
                        <div>
                            <h2 class="ud-title">{{ $t['notes'] }}</h2>
                            <p class="ud-sub">{{ $t['notes_sub'] }}</p>
                        </div>
                        @if ($unreadShown > 0)
                            <span class="ud-chip">{{ str_replace(':count', $digits($unreadShown), $t['notes_new']) }}</span>
                        @endif
                    </div>
                    <a href="{{ route('notifications.index') }}" wire:navigate class="ud-more">{{ $t['notes_all'] }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                @if ($latestNotifications->count())
                    <ul class="ud-notes">
                        @foreach ($latestNotifications as $notification)
                            @php
                                [$icon, $tone] = $kind((string) $notification->notification_text);
                                $at = $notification->created_at->timezone('Asia/Dhaka');
                                $sender = $notification->userFrom;
                                $senderName = $sender ? ($isBn && trim((string) $sender->name_bn) !== '' ? $sender->name_bn : $sender->name) : '';
                            @endphp
                            <li class="ud-note {{ $notification->is_read ? '' : 'is-unread' }}">
                                <a href="{{ route('notifications.read', $notification->id) }}">
                                    <span class="ud-note-icon {{ $tone }}" aria-hidden="true"><i class="fas {{ $icon }}"></i></span>
                                    <span class="ud-note-main">
                                        <p class="ud-note-text">{{ $notification->notification_text }}</p>
                                        @if ($senderName !== '')
                                            <span class="ud-note-from"><i class="fas fa-user" aria-hidden="true"></i>{{ $senderName }}</span>
                                        @endif
                                    </span>
                                    <span class="ud-note-side">
                                        @if (! $notification->is_read)
                                            <span class="ud-note-new">{{ $t['new_badge'] }}</span>
                                        @endif
                                        <strong>{{ $digits($at->copy()->locale($isBn ? 'bn' : 'en')->diffForHumans()) }}</strong>
                                        <span>{{ $digits($at->copy()->locale($isBn ? 'bn' : 'en')->translatedFormat($isBn ? 'j F Y, g:i A' : 'd M Y, h:i A')) }}</span>
                                    </span>
                                    <i class="fas fa-chevron-right ud-note-go" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="ud-empty">
                        <i class="fas fa-bell-slash" aria-hidden="true"></i>
                        <span>{{ $t['notes_empty'] }}</span>
                    </div>
                @endif
            </div>

            {{-- ============ Requisitions per month (full width, under the notifications) ============ --}}
            @php
                $chartRows = $months->map(function ($row) use ($isBn, $digits) {
                    return [
                        'short' => $row['month']->copy()->locale($isBn ? 'bn' : 'en')->translatedFormat('F') . ' ' . $digits($row['month']->format('Y')),
                        'long' => $row['month']->copy()->locale($isBn ? 'bn' : 'en')->translatedFormat('F') . ' ' . $digits($row['month']->format('Y')),
                        'total' => $row['total'],
                        'now' => $row['month']->isSameMonth(now()),
                    ];
                })->values();
            @endphp
            <div class="ud-card ud-section">
                <div class="ud-head">
                    <div class="ud-head-main">
                        <span class="ud-head-icon" aria-hidden="true"><i class="fas fa-chart-bar"></i></span>
                        <div>
                            <h2 class="ud-title">{{ $t['chart'] }}</h2>
                            <p class="ud-sub">{{ $t['chart_sub'] }} · {{ str_replace(':count', $digits($chartTotal), $t['chart_total']) }}</p>
                        </div>
                    </div>
                    @if ($listUrl)
                        <a href="{{ $listUrl }}" wire:navigate class="ud-more">{{ $t['see_list'] }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    @endif
                </div>
                <div class="ud-chart-box" id="ud-chart-box">
                    <canvas id="ud-chart" role="img" aria-label="{{ $t['chart'] }}"></canvas>
                    <div class="ud-plain">
                        <ol class="ud-chart">
                            @foreach ($chartRows as $row)
                                <li class="ud-bar-col" title="{{ strtr($t['bar'], [':month' => $row['long'], ':count' => $digits($row['total'])]) }}">
                                    <span class="ud-bar-value">{{ $row['total'] > 0 ? $digits($row['total']) : '' }}</span>
                                    <span class="ud-bar {{ $row['total'] === 0 ? 'is-empty' : '' }} {{ $row['now'] && $row['total'] > 0 ? 'is-now' : '' }}"
                                        style="height: {{ $row['total'] === 0 ? 0 : max(4, round($row['total'] / $peak * 100, 1)) }}%"></span>
                                    <span class="sr-only">{{ strtr($t['bar'], [':month' => $row['long'], ':count' => $digits($row['total'])]) }}</span>
                                </li>
                            @endforeach
                        </ol>
                        <ol class="ud-months" aria-hidden="true">
                            @foreach ($months as $row)
                                <li class="{{ $row['month']->isSameMonth(now()) ? 'is-now' : '' }}">
                                    {{ $row['month']->copy()->locale($isBn ? 'bn' : 'en')->translatedFormat('F') }}
                                    <small>{{ $digits($row['month']->format('Y')) }}</small>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

{{-- The chart library (the same one the main dashboard loads) --}}
@push('head_scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush

@push('js')
    <script>
        // Draws the monthly chart once the chart library is there. Until then - and if the
        // library cannot be loaded at all - the plain bars under it stay on the page.
        (function () {
            var rows = @json($chartRows);
            var useBangla = @json($isBn);
            var texts = { tooltip: @json($t['tooltip']), axis: @json($t['axis']) };
            var digits = function (value) {
                return useBangla ? String(value).replace(/[0-9]/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; }) : String(value);
            };
            var tries = 0;

            function draw() {
                var box = document.getElementById('ud-chart-box'), canvas = document.getElementById('ud-chart');
                if (!box || !canvas) { return; }
                if (typeof window.Chart === 'undefined') {
                    if (tries++ < 50) { setTimeout(draw, 120); }
                    return;
                }
                if (window.__udChart) { window.__udChart.destroy(); }
                box.classList.add('is-ready');

                var context = canvas.getContext('2d');
                var fill = function (from, to) {
                    var gradient = context.createLinearGradient(0, 0, 0, box.clientHeight);
                    gradient.addColorStop(0, from);
                    gradient.addColorStop(1, to);
                    return gradient;
                };
                var blue = fill('#5b9bff', '#1d4fd8'), indigo = fill('#a5a8ff', '#4f46e5');
                // On a phone: smaller text, month names without the year, no axis caption
                var narrow = box.clientWidth < 560;
                var font = { family: getComputedStyle(document.body).fontFamily, size: narrow ? 11 : 15, weight: '600' };

                window.__udChart = new Chart(context, {
                    type: 'bar',
                    data: {
                        labels: rows.map(function (row) { return narrow ? row.short.split(' ') : row.short; }),
                        datasets: [{
                            data: rows.map(function (row) { return row.total; }),
                            backgroundColor: rows.map(function (row) { return row.now ? indigo : blue; }),
                            hoverBackgroundColor: rows.map(function (row) { return row.now ? '#4338ca' : '#1a3f9f'; }),
                            borderRadius: 10,
                            borderSkipped: false,
                            maxBarThickness: 110,
                            categoryPercentage: 0.8,
                            barPercentage: 0.85
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        layout: { padding: { top: 30 } },
                        animation: { duration: 700, easing: 'easeOutQuart' },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 12,
                                cornerRadius: 10,
                                displayColors: false,
                                titleFont: font,
                                bodyFont: font,
                                callbacks: {
                                    title: function (items) { return rows[items[0].dataIndex].long; },
                                    label: function (item) { return texts.tooltip.replace(':count', digits(item.parsed.y)); }
                                }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, border: { color: '#e2e8f0' }, ticks: { color: '#64748b', font: font, maxRotation: 0, autoSkip: false } },
                            y: {
                                beginAtZero: true,
                                suggestedMax: Math.max(4, Math.max.apply(null, rows.map(function (row) { return row.total; })) + 1),
                                border: { display: false, dash: [4, 6] },
                                grid: { color: '#e2e8f0' },
                                title: { display: !narrow, text: texts.axis, color: '#94a3b8', font: font },
                                ticks: { precision: 0, color: '#64748b', font: font, callback: function (value) { return digits(value); } }
                            }
                        }
                    },
                    // The figure above each bar
                    plugins: [{
                        id: 'barValues',
                        afterDatasetsDraw: function (chart) {
                            var ctx = chart.ctx;
                            ctx.save();
                            ctx.font = '700 ' + (narrow ? 12 : 16) + 'px ' + font.family;
                            ctx.fillStyle = '#334155';
                            ctx.textAlign = 'center';
                            chart.getDatasetMeta(0).data.forEach(function (bar, index) {
                                if (rows[index].total > 0) { ctx.fillText(digits(rows[index].total), bar.x, bar.y - 8); }
                            });
                            ctx.restore();
                        }
                    }]
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', draw);
            } else {
                draw();
            }
        })();
    </script>
@endpush
