@extends('layouts.app')

@push('page_css')
    <style>
        /* ---- Notifications page ---- */
        .notify-page { padding: 1rem 0.5rem 1.5rem; }
        .notify-page .np-head { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.5rem 1rem; margin-bottom: 1rem; }
        .notify-page .np-crumb { display: flex; flex-wrap: wrap; gap: 0.4rem; margin: 0; padding: 0; list-style: none; color: #64748b; font-size: 0.875rem; }
        .notify-page .np-crumb a { color: #1d65db; font-weight: 500; }
        .notify-page .np-crumb li + li::before { content: "/"; margin-right: 0.4rem; color: #cbd5e1; }
        .notify-page .np-head-text { text-align: right; }
        .notify-page .np-title { margin: 0; color: #0f172a; font-size: 1.5rem; font-weight: 700; line-height: 1.25; }
        .notify-page .np-subtitle { margin: 0.15rem 0 0; color: #64748b; font-size: 0.875rem; }

        .notify-page .np-card { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 1rem; background: #fff; box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 12px 28px -18px rgba(15, 23, 42, 0.25); }

        /* Toolbar: counts on the left, "mark all as read" on the right */
        .notify-page .np-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.9rem 1.25rem; border-bottom: 1px solid #eef2f7; }
        .notify-page .np-stats { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
        .notify-page .np-chip { display: inline-flex; align-items: center; gap: 0.4rem; padding: 0.3rem 0.75rem; border: 1px solid #e2e8f0; border-radius: 999px; background: #f8fafc; color: #475569; font-size: 0.8125rem; font-weight: 600; }
        .notify-page .np-chip.is-unread { border-color: #c7d9fb; background: #eef4ff; color: #1d4fd8; }
        .notify-page .np-chip.is-unread::before { content: ""; width: 0.45rem; height: 0.45rem; border-radius: 999px; background: currentColor; }
        .notify-page .np-bar form { margin: 0; }
        .notify-page .np-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; min-height: 2.4rem; padding: 0 1rem; border: 1px solid #1d65db; border-radius: 0.6rem; background: #1d65db linear-gradient(to bottom, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0)); color: #fff; font-size: 0.875rem; font-weight: 600; cursor: pointer; box-shadow: 0 6px 14px -6px rgba(37, 99, 235, 0.7); transition: background-color 0.15s, border-color 0.15s, box-shadow 0.15s; }
        .notify-page .np-btn:hover { border-color: #1a51b0; background-color: #1a51b0; }
        .notify-page .np-btn:focus-visible { outline: 0; box-shadow: 0 0 0 4px rgba(47, 124, 246, 0.3); }
        .notify-page .np-btn:disabled { border-color: #e2e8f0; background: #f1f5f9; color: #94a3b8; box-shadow: none; cursor: not-allowed; }

        /* List */
        .notify-page .np-list { margin: 0; padding: 0; list-style: none; }
        .notify-page .np-item { border-bottom: 1px solid #eef2f7; }
        .notify-page .np-item:last-child { border-bottom: 0; }
        .notify-page .np-link { position: relative; display: flex; align-items: flex-start; gap: 0.9rem; padding: 0.95rem 1.25rem; color: inherit; text-decoration: none; transition: background-color 0.15s; }
        .notify-page .np-link:hover { background: #f8fafc; color: inherit; text-decoration: none; }
        .notify-page .np-link:focus-visible { outline: 0; box-shadow: inset 0 0 0 3px rgba(47, 124, 246, 0.35); }
        .notify-page .np-item.is-unread .np-link { background: #f5f8ff; }
        .notify-page .np-item.is-unread .np-link:hover { background: #ecf2ff; }
        .notify-page .np-item.is-unread .np-link::before { content: ""; position: absolute; top: 0; bottom: 0; left: 0; width: 3px; background: #1d65db; }

        .notify-page .np-icon { display: inline-flex; flex: 0 0 auto; align-items: center; justify-content: center; width: 2.6rem; height: 2.6rem; border-radius: 0.8rem; font-size: 1.05rem; }
        .notify-page .np-icon.is-success { background: #ecfdf5; color: #059669; }
        .notify-page .np-icon.is-info { background: #eef4ff; color: #1d65db; }
        .notify-page .np-icon.is-danger { background: #fff1f2; color: #e11d48; }
        .notify-page .np-icon.is-warning { background: #fffbeb; color: #d97706; }
        .notify-page .np-icon.is-neutral { background: #f1f5f9; color: #475569; }

        .notify-page .np-body { flex: 1 1 auto; min-width: 0; }
        .notify-page .np-text { margin: 0; color: #334155; font-size: 0.9375rem; line-height: 1.5; overflow-wrap: anywhere; }
        .notify-page .np-item.is-unread .np-text { color: #0f172a; font-weight: 600; }
        .notify-page .np-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 0.25rem 0.9rem; margin-top: 0.3rem; color: #64748b; font-size: 0.8125rem; }
        .notify-page .np-meta span { display: inline-flex; align-items: center; gap: 0.35rem; }
        .notify-page .np-meta i { color: #94a3b8; font-size: 0.75rem; }
        .notify-page .np-state { flex: 0 0 auto; align-self: center; padding: 0.2rem 0.6rem; border-radius: 999px; background: #1d65db; color: #fff; font-size: 0.6875rem; font-weight: 700; letter-spacing: 0.02em; }
        .notify-page .np-chevron { flex: 0 0 auto; align-self: center; color: #cbd5e1; font-size: 0.8rem; }

        /* Nothing to show */
        .notify-page .np-empty { display: flex; flex-direction: column; align-items: center; gap: 0.6rem; padding: 3rem 1.25rem; text-align: center; }
        .notify-page .np-empty-icon { display: inline-flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; border-radius: 999px; background: #f1f5f9; color: #94a3b8; font-size: 1.5rem; }
        .notify-page .np-empty p { margin: 0; color: #64748b; font-size: 0.9375rem; font-weight: 500; }

        /* Pages */
        .notify-page .np-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.85rem 1.25rem; border-top: 1px solid #eef2f7; background: #f8fafc; }
        .notify-page .np-range { margin: 0; color: #64748b; font-size: 0.8125rem; }
        .notify-page .np-foot .pagination { flex-wrap: wrap; gap: 4px; margin: 0; }
        .notify-page .np-foot .page-link { display: inline-flex; align-items: center; justify-content: center; min-width: 2.25rem; height: 2.25rem; padding: 0 0.6rem; border: 1px solid #e2e8f0; border-radius: 0.55rem !important; background: #fff; color: #334155; font-size: 0.875rem; font-weight: 600; }
        .notify-page .np-foot .page-link:hover { background: #eef4ff; color: #1d4fd8; }
        .notify-page .np-foot .page-item.active .page-link { border-color: #1d65db; background: #1d65db; color: #fff; }
        .notify-page .np-foot .page-item.disabled .page-link { background: #f8fafc; color: #cbd5e1; }

        @media (max-width: 575.98px) {
            .notify-page .np-head-text { width: 100%; text-align: left; }
            .notify-page .np-bar, .notify-page .np-link, .notify-page .np-foot { padding-right: 1rem; padding-left: 1rem; }
            .notify-page .np-bar form, .notify-page .np-btn { width: 100%; }
            .notify-page .np-chevron { display: none; }
            .notify-page .np-foot { justify-content: center; }
        }
    </style>
@endpush

@section('content')
    @php
        $npBn = app()->getLocale() === 'bn';
        $npT = $npBn
            ? ['home' => 'হোম', 'title' => 'নোটিফিকেশন', 'subtitle' => 'আপনার সব নোটিফিকেশন এক জায়গায়।', 'unread' => 'টি অপঠিত', 'total' => 'মোট', 'total_unit' => 'টি', 'mark_all' => 'সব পঠিত হিসেবে চিহ্নিত করুন', 'new' => 'নতুন', 'system' => 'সিস্টেম', 'empty' => 'কোনো নোটিফিকেশন পাওয়া যায়নি।', 'range' => 'মোট :total টির মধ্যে :from–:to দেখানো হচ্ছে']
            : ['home' => 'Home', 'title' => 'Notifications', 'subtitle' => 'All your notifications in one place.', 'unread' => ' unread', 'total' => 'Total', 'total_unit' => '', 'mark_all' => 'Mark all as read', 'new' => 'NEW', 'system' => 'System', 'empty' => 'No notifications found.', 'range' => 'Showing :from–:to of :total'];
        $npDigits = fn ($value) => $npBn
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;
        // Icon and colour by what the notification is about (same as the bell's list)
        $npKind = function (string $text) {
            $lower = strtolower($text);
            return match (true) {
                str_contains($lower, 'reject') => ['fa-times-circle', 'is-danger'],
                str_contains($lower, 'deliver') => ['fa-truck', 'is-success'],
                str_contains($lower, 'approved') => ['fa-check-circle', 'is-info'],
                str_contains($lower, 'pending') => ['fa-hourglass-half', 'is-warning'],
                default => ['fa-bell', 'is-neutral'],
            };
        };
        // Unread notifications of the signed-in user (all pages, not only this one)
        $npUnread = app(\App\Services\NotificationService::class)->getUnreadCount(Auth::id());
    @endphp

    <section class="content notify-page">
        <div class="container-fluid">
            <div class="np-head">
                <ol class="np-crumb">
                    <li><a href="{{ route('home') }}">{{ $npT['home'] }}</a></li>
                    <li aria-current="page">{{ $npT['title'] }}</li>
                </ol>
                <div class="np-head-text">
                    <h1 class="np-title">{{ $npT['title'] }}</h1>
                    <p class="np-subtitle">{{ $npT['subtitle'] }}</p>
                </div>
            </div>

            <div class="np-card">
                <div class="np-bar">
                    <div class="np-stats">
                        <span class="np-chip">{{ $npT['total'] }} {{ $npDigits($notifications->total()) }}{{ $npT['total_unit'] }}</span>
                        @if ($npUnread > 0)
                            <span class="np-chip is-unread">{{ $npDigits($npUnread) }}{{ $npT['unread'] }}</span>
                        @endif
                    </div>
                    <form action="{{ route('notifications.read_all') }}" method="POST">
                        @csrf
                        <button type="submit" class="np-btn" @disabled($npUnread === 0)>
                            <i class="fas fa-check-double" aria-hidden="true"></i> {{ $npT['mark_all'] }}
                        </button>
                    </form>
                </div>

                @if ($notifications->count())
                    <ul class="np-list">
                        @foreach ($notifications as $notification)
                            @php
                                [$npIcon, $npTone] = $npKind((string) $notification->notification_text);
                                $npAt = $notification->created_at->timezone('Asia/Dhaka');
                                $sender = $notification->userFrom;
                                $npSender = $sender ? ($npBn && trim((string) $sender->name_bn) !== '' ? $sender->name_bn : $sender->name) : $npT['system'];
                            @endphp
                            <li class="np-item {{ $notification->is_read ? '' : 'is-unread' }}">
                                <a href="{{ route('notifications.read', $notification->id) }}" class="np-link">
                                    <span class="np-icon {{ $npTone }}" aria-hidden="true"><i class="fas {{ $npIcon }}"></i></span>
                                    <span class="np-body">
                                        <p class="np-text">{{ $notification->notification_text }}</p>
                                        <span class="np-meta">
                                            <span><i class="fas fa-user" aria-hidden="true"></i> {{ $npSender }}</span>
                                            <span title="{{ $npAt->format('d M, Y h:i A') }}"><i class="fas fa-clock" aria-hidden="true"></i> {{ $npDigits($npAt->copy()->locale($npBn ? 'bn' : 'en')->diffForHumans()) }}</span>
                                            <span><i class="fas fa-calendar-alt" aria-hidden="true"></i> {{ $npDigits($npAt->copy()->locale($npBn ? 'bn' : 'en')->translatedFormat($npBn ? 'j F Y, g:i A' : 'd M, Y h:i A')) }}</span>
                                        </span>
                                    </span>
                                    @if (! $notification->is_read)
                                        <span class="np-state">{{ $npT['new'] }}</span>
                                    @endif
                                    <i class="fas fa-chevron-right np-chevron" aria-hidden="true"></i>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="np-empty">
                        <span class="np-empty-icon" aria-hidden="true"><i class="fas fa-bell-slash"></i></span>
                        <p>{{ $npT['empty'] }}</p>
                    </div>
                @endif

                @if ($notifications->total() > 0)
                    <div class="np-foot">
                        <p class="np-range">{{ strtr($npT['range'], [':total' => $npDigits($notifications->total()), ':from' => $npDigits($notifications->firstItem()), ':to' => $npDigits($notifications->lastItem())]) }}</p>
                        {{ $notifications->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
