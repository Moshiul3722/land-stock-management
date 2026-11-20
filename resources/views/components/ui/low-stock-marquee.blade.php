{{--
    Low-stock notice: a scrolling line under the top bar naming every product whose total
    stock is below 5. Shown on every page, to Super Admin, Admin and Store Admin only.
    Its label opens the full list, with print preview and print (LowStockController).
    Used in resources/views/layouts/app.blade.php.
--}}
@php
    $lowStockUser = auth()->user();
    $lowStockItems = collect();

    if (\App\Support\LowStock::canSee($lowStockUser)) {
        $isBn = app()->getLocale() === 'bn';
        $digits = fn ($value) => \App\Support\LowStock::digits($value);
        $lowStockLimit = \App\Support\LowStock::LIMIT;

        $lowStockItems = \App\Support\LowStock::items()->map(fn ($item) => $item + [
            'amount' => trim($digits($item['total']) . ' ' . $item['unit']),
        ]);

        $lowStockTexts = $isBn ? [
            'label' => 'স্টক কম',
            'hint' => 'যেসব মালামালের স্টক ' . $digits($lowStockLimit) . '-এর কম',
            'out' => 'শেষ',
            'count' => $digits($lowStockItems->count()) . 'টি মালামাল',
            'open' => 'স্টক কম মালামালের তালিকা দেখুন',
            'title' => 'স্টক কম মালামালের তালিকা',
            'serial' => 'ক্রমিক',
            'product' => 'মালামাল',
            'stock' => 'বর্তমান স্টক',
            'status' => 'অবস্থা',
            'low' => 'কম',
            'preview' => 'প্রিন্ট প্রিভিউ',
            'print' => 'প্রিন্ট',
            'close' => 'বন্ধ করুন',
            'prev' => 'আগের পাতা',
            'next' => 'পরের পাতা',
            'showing' => 'মোট :totalটির মধ্যে :from থেকে :to পর্যন্ত দেখানো হচ্ছে',
        ] : [
            'label' => 'Low stock',
            'hint' => 'Products with less than ' . $lowStockLimit . ' in stock',
            'out' => 'Out',
            'count' => $lowStockItems->count() . ' products',
            'open' => 'Show the low-stock list',
            'title' => 'Low-stock products',
            'serial' => 'No.',
            'product' => 'Product',
            'stock' => 'Current stock',
            'status' => 'Status',
            'low' => 'Low',
            'preview' => 'Print preview',
            'print' => 'Print',
            'close' => 'Close',
            'prev' => 'Previous page',
            'next' => 'Next page',
            'showing' => 'Showing :from to :to of :total',
        ];
        $lowStockPerPage = 10;
        $canOpenStock = app(\App\Services\MenuPermissionService::class)->allowsRoute($lowStockUser, 'stock.show');
        // About 4 seconds per product, so the speed stays readable however long the list is
        $lowStockSeconds = max(25, $lowStockItems->count() * 4);
    }
@endphp

@if ($lowStockItems->isNotEmpty())
    <div class="low-stock" role="region" aria-label="{{ $lowStockTexts['hint'] }}">
        <button type="button" class="low-stock-label" title="{{ $lowStockTexts['open'] }}" aria-label="{{ $lowStockTexts['open'] }}"
            data-toggle="modal" data-target="#low-stock-modal">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <span>{{ $lowStockTexts['label'] }}</span>
            <span class="low-stock-count">{{ $lowStockTexts['count'] }}</span>
        </button>
        <div class="low-stock-track">
            {{-- The list twice in a row: when the first copy has scrolled away the second one is
                 exactly where the first started, so the line runs on without a jump. --}}
            <div class="low-stock-belt" style="--low-stock-seconds: {{ $lowStockSeconds }}s">
                @foreach ([false, true] as $copy)
                    <ul class="low-stock-list" @if ($copy) aria-hidden="true" @endif>
                        @foreach ($lowStockItems as $item)
                            <li class="low-stock-item {{ $item['out'] ? 'is-out' : '' }}">
                                @if ($canOpenStock)
                                    <a href="{{ route('stock.show', $item['id']) }}" wire:navigate @if ($copy) tabindex="-1" @endif>
                                @endif
                                <span class="low-stock-name">{{ $item['name'] }}</span>
                                <span class="low-stock-amount">{{ $item['out'] ? $lowStockTexts['out'] : $item['amount'] }}</span>
                                @if ($canOpenStock)
                                    </a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
        </div>
    </div>

    {{-- The whole list, with print preview and print --}}
    <div class="modal fade low-stock-modal" id="low-stock-modal" tabindex="-1" role="dialog" aria-labelledby="low-stock-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <span class="low-stock-modal-icon" aria-hidden="true"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="mr-auto">
                        <h5 class="modal-title mb-0" id="low-stock-modal-title">{{ $lowStockTexts['title'] }}</h5>
                        <small class="text-muted">{{ $lowStockTexts['hint'] }} · {{ $lowStockTexts['count'] }}</small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ $lowStockTexts['close'] }}"><span aria-hidden="true">&times;</span></button>
                </div>
                {{-- 10 products at a time, with page buttons under the list --}}
                <div class="modal-body p-0" x-data="{ page: 0, per: {{ $lowStockPerPage }}, total: {{ $lowStockItems->count() }},
                        get pages() { return Math.max(1, Math.ceil(this.total / this.per)) },
                        bn(n) { return @js($isBn) ? String(n).replace(/[0-9]/g, (d) => '০১২৩৪৫৬৭৮৯'[d]) : String(n) },
                        go(p) { this.page = Math.min(Math.max(0, p), this.pages - 1); this.$refs.list.scrollTop = 0 } }"
                    x-on:show.bs.modal.window="page = 0">
                    <div class="table-responsive" x-ref="list">
                        <table class="table table-hover mb-0 low-stock-table">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4.5rem;">{{ $lowStockTexts['serial'] }}</th>
                                    <th>{{ $lowStockTexts['product'] }}</th>
                                    <th class="text-center">{{ $lowStockTexts['stock'] }}</th>
                                    <th class="text-center">{{ $lowStockTexts['status'] }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockItems as $item)
                                    <tr x-show="Math.floor({{ $loop->index }} / per) === page">
                                        <td class="text-center">{{ $digits($loop->iteration) }}</td>
                                        <td class="font-weight-bold">
                                            @if ($canOpenStock)
                                                <a href="{{ route('stock.show', $item['id']) }}" wire:navigate class="text-body">{{ $item['name'] }}</a>
                                            @else
                                                {{ $item['name'] }}
                                            @endif
                                        </td>
                                        <td class="text-center text-nowrap">{{ $item['amount'] }}</td>
                                        <td class="text-center">
                                            <span class="low-stock-badge {{ $item['out'] ? 'is-out' : '' }}">{{ $item['out'] ? $lowStockTexts['out'] : $lowStockTexts['low'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($lowStockItems->count() > $lowStockPerPage)
                        <nav class="low-stock-pager" aria-label="{{ $lowStockTexts['title'] }}">
                            <span class="low-stock-pager-info" aria-live="polite"
                                x-text="@js($lowStockTexts['showing']).replace(':total', bn(total)).replace(':from', bn(page * per + 1)).replace(':to', bn(Math.min(total, (page + 1) * per)))"></span>
                            <span class="low-stock-pager-buttons">
                                <button type="button" class="low-stock-page-btn" x-on:click="go(page - 1)" x-bind:disabled="page === 0"
                                    aria-label="{{ $lowStockTexts['prev'] }}" title="{{ $lowStockTexts['prev'] }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                                <template x-for="p in pages" x-bind:key="p">
                                    <button type="button" class="low-stock-page-btn" x-on:click="go(p - 1)" x-bind:class="p - 1 === page && 'is-active'"
                                        x-bind:aria-current="p - 1 === page ? 'page' : null" x-text="bn(p)"></button>
                                </template>
                                <button type="button" class="low-stock-page-btn" x-on:click="go(page + 1)" x-bind:disabled="page >= pages - 1"
                                    aria-label="{{ $lowStockTexts['next'] }}" title="{{ $lowStockTexts['next'] }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                            </span>
                        </nav>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">{{ $lowStockTexts['close'] }}</button>
                    <a href="{{ route('low-stock.print', ['preview' => 1]) }}" target="_blank" rel="noopener" class="btn btn-outline-primary">
                        <i class="fas fa-file-alt mr-1" aria-hidden="true"></i>{{ $lowStockTexts['preview'] }}
                    </a>
                    <a href="{{ route('low-stock.print') }}" target="_blank" rel="noopener" class="btn btn-primary">
                        <i class="fas fa-print mr-1" aria-hidden="true"></i>{{ $lowStockTexts['print'] }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        .low-stock {
            display: flex;
            align-items: stretch;
            overflow: hidden;
            border-bottom: 1px solid #fde68a;
            background: linear-gradient(90deg, #fffbeb, #fff7ed);
            color: #78350f;
            font-size: 0.875rem;
            line-height: 1.3;
        }

        .low-stock-label {
            position: relative;
            z-index: 1;
            display: inline-flex;
            flex: 0 0 auto;
            align-items: center;
            gap: 0.45rem;
            padding: 0.45rem 0.9rem;
            background: linear-gradient(120deg, #b45309, #f59e0b);
            color: #fff;
            font-weight: 700;
            white-space: nowrap;
            box-shadow: 6px 0 12px -6px rgba(120, 53, 15, 0.45);
        }

        button.low-stock-label {
            border: 0;
            cursor: pointer;
            font: inherit;
            font-weight: 700;
        }

        button.low-stock-label:hover {
            background: linear-gradient(120deg, #92400e, #d97706);
        }

        button.low-stock-label:focus-visible {
            outline: 2px solid #fff;
            outline-offset: -4px;
        }

        .low-stock-modal .modal-header {
            align-items: center;
            gap: 0.75rem;
        }

        .low-stock-modal-icon {
            display: inline-flex;
            flex: none;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            background: #fef3c7;
            color: #b45309;
        }

        .low-stock-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: #f8fafc;
            border-top: 0;
            font-size: 0.8125rem;
            color: #475569;
        }

        .low-stock-table td {
            vertical-align: middle;
        }

        .low-stock-pager {
            position: sticky;
            bottom: 0;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem 1rem;
            padding: 0.65rem 1rem;
            border-top: 1px solid #e2e8f0;
            background: #fff;
            font-size: 0.8125rem;
            color: #475569;
        }

        .low-stock-pager-buttons {
            display: inline-flex;
            gap: 0.3rem;
        }

        .low-stock-page-btn {
            min-width: 2.1rem;
            height: 2.1rem;
            padding: 0 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.5rem;
            background: #fff;
            color: #334155;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        .low-stock-page-btn:hover:not(:disabled) {
            background: #f1f5f9;
        }

        .low-stock-page-btn.is-active {
            border-color: #2f7250;
            background: #2f7250;
            color: #fff;
        }

        .low-stock-page-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        .low-stock-badge {
            display: inline-block;
            padding: 0.1rem 0.6rem;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.8125rem;
            font-weight: 700;
        }

        .low-stock-badge.is-out {
            background: #fee2e2;
            color: #b91c1c;
        }

        @media (max-width: 575.98px) {
            .low-stock-modal .modal-footer {
                flex-wrap: wrap;
            }

            .low-stock-modal .modal-footer > * {
                flex: 1 1 auto;
            }
        }

        .low-stock-count {
            padding: 0.05rem 0.45rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.22);
            font-size: 0.75rem;
            font-weight: 600;
        }

        .low-stock-track {
            position: relative;
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            /* Fade the line in and out at both ends */
            -webkit-mask-image: linear-gradient(90deg, transparent, #000 2rem, #000 calc(100% - 2rem), transparent);
            mask-image: linear-gradient(90deg, transparent, #000 2rem, #000 calc(100% - 2rem), transparent);
        }

        .low-stock-belt {
            display: flex;
            width: max-content;
            animation: low-stock-scroll var(--low-stock-seconds, 40s) linear infinite;
        }

        /* Hold still while the pointer or keyboard focus is on it, so a name can be read or clicked */
        .low-stock-track:hover .low-stock-belt,
        .low-stock-track:focus-within .low-stock-belt {
            animation-play-state: paused;
        }

        .low-stock-list {
            display: flex;
            flex: 0 0 auto;
            align-items: center;
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .low-stock-item {
            display: inline-flex;
            align-items: center;
            padding: 0.45rem 0;
            white-space: nowrap;
        }

        .low-stock-item::after {
            content: "";
            width: 5px;
            height: 5px;
            margin: 0 1.1rem;
            border-radius: 50%;
            background: #f59e0b;
        }

        .low-stock-item a {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            border-radius: 0.4rem;
            color: inherit;
        }

        .low-stock-item a:hover,
        .low-stock-item a:focus-visible {
            color: #92400e;
            text-decoration: underline;
            outline: 0;
        }

        .low-stock-item > .low-stock-name {
            margin-right: 0.4rem;
        }

        .low-stock-name {
            font-weight: 600;
        }

        .low-stock-amount {
            padding: 0.05rem 0.5rem;
            border-radius: 999px;
            background: #fef3c7;
            color: #92400e;
            font-size: 0.8125rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .low-stock-item.is-out .low-stock-amount {
            background: #fee2e2;
            color: #b91c1c;
        }

        @keyframes low-stock-scroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        @media (max-width: 575.98px) {
            .low-stock {
                font-size: 0.8125rem;
            }

            .low-stock-label {
                padding: 0.4rem 0.6rem;
            }

            /* Phones: just the icon and the count, so the names get the room */
            .low-stock-label > span:not(.low-stock-count) {
                display: none;
            }
        }

        /* Visitors who asked for less motion: no scrolling, the line can be swiped instead */
        @media (prefers-reduced-motion: reduce) {
            .low-stock-track {
                overflow-x: auto;
            }

            .low-stock-belt {
                animation: none;
            }

            .low-stock-list[aria-hidden="true"] {
                display: none;
            }
        }

        @media print {
            .low-stock {
                display: none;
            }
        }
    </style>
@endif
