@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $digits = fn ($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
    $unitLabels = $unitLabels ?? collect();
@endphp
<table id="current-stock-table" class="table" style="width:100%;">
    <thead>
        <tr>
            <th>{{ __('stock.table_sl') }}</th>
            <th>{{ __('stock.table_category') }}</th>
            <th>{{ __('subcategory.title') }}</th>
            <th>{{ __('stock.table_product') }}</th>
            <th>{{ $isEn ? 'Stocked Quantity' : 'স্টককৃত মালামালের পরিমাণ' }}</th>
        </tr>
    </thead>
    <tbody>

        @foreach ($stockSummary as $item)
            {{-- Logic: 0 = Red, < 10 = Yellow, >= 10 = Green --}}

            <tr
                class="{{ $item->total_quantity == 0 ? 'text-danger' : ($item->total_quantity < 10 ? 'text-warning' : 'text') }}">
                <td class="text-center">{{ $digits($loop->iteration) }}</td>
                <td>{{ $item->category_name }}</td>
                <td>{{ $item->sub_category_name ?? '-' }}</td>
                <td>{{ $item->product_name }}</td>
                {{-- Quantity with the product's unit --}}
                <td data-order="{{ (float) $item->total_quantity }}">
                    {{ $digits($item->total_quantity) }}@if (($unitLabels[$item->product_id] ?? '') !== '') <span class="font-weight-normal">{{ $unitLabels[$item->product_id] }}</span>@endif
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="bg-light font-weight-bold">
            <td colspan="4" class="text-right">{{ $isEn ? 'Total Quantity in View:' : 'মোট পরিমাণ:' }}</td>
            <td class="text-center">{{ $digits($stockSummary->sum('total_quantity')) }}</td>
        </tr>
    </tfoot>
</table>
