{{--
    Low-stock notice: a scrolling line under the top bar naming every product whose total
    stock is below 5. Shown on every page, to Super Admin, Admin and Store Admin only.
    Used in resources/views/layouts/app.blade.php.
--}}
@php
    $lowStockRoles = [1, 2, 3];
    $lowStockLimit = 5;
    $lowStockUser = auth()->user();
    $lowStockItems = collect();

    if ($lowStockUser && in_array((int) $lowStockUser->role_id, $lowStockRoles, true)) {
        $isBn = app()->getLocale() === 'bn';
        $digits = fn ($value) => $isBn
            ? strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'])
            : (string) $value;

        // Total stock of each product that has ever been stocked (same rows as the stock list)
        $lowStockItems = \Illuminate\Support\Facades\DB::table('stocks')
            ->join('products', 'products.id', '=', 'stocks.product_id')
            ->whereNull('stocks.deleted_at')
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.name', 'products.name_bn', 'products.product_unit')
            ->havingRaw('SUM(stocks.quantity) < ?', [$lowStockLimit])
            ->orderByRaw('SUM(stocks.quantity)')
            ->orderBy('products.name')
            ->selectRaw('products.id, products.name, products.name_bn, products.product_unit, SUM(stocks.quantity) as total')
            ->get()
            ->map(function ($row) use ($isBn, $digits) {
                $total = max(0, (int) $row->total);
                $name = $isBn && trim((string) $row->name_bn) !== '' ? $row->name_bn : $row->name;
                $unit = $row->product_unit && ! is_numeric($row->product_unit) ? \App\Models\ProductUnit::label($row->product_unit) : '';

                return [
                    'id' => (int) $row->id,
                    'name' => $name,
                    'amount' => trim($digits($total) . ' ' . $unit),
                    'out' => $total === 0,
                ];
            });

        $lowStockTexts = $isBn ? [
            'label' => 'স্টক কম',
            'hint' => 'যেসব মালামালের স্টক ' . $digits($lowStockLimit) . '-এর কম',
            'out' => 'শেষ',
            'count' => $digits($lowStockItems->count()) . 'টি মালামাল',
        ] : [
            'label' => 'Low stock',
            'hint' => 'Products with less than ' . $lowStockLimit . ' in stock',
            'out' => 'Out',
            'count' => $lowStockItems->count() . ' products',
        ];
        $canOpenStock = app(\App\Services\MenuPermissionService::class)->allowsRoute($lowStockUser, 'stock.show');
        // About 4 seconds per product, so the speed stays readable however long the list is
        $lowStockSeconds = max(25, $lowStockItems->count() * 4);
    }
@endphp

@if ($lowStockItems->isNotEmpty())
    <div class="low-stock" role="region" aria-label="{{ $lowStockTexts['hint'] }}">
        <span class="low-stock-label" title="{{ $lowStockTexts['hint'] }}">
            <i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
            <span>{{ $lowStockTexts['label'] }}</span>
            <span class="low-stock-count">{{ $lowStockTexts['count'] }}</span>
        </span>
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
