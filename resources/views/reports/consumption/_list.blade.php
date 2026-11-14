@php
    $isEn = app()->getLocale() === 'en';
    $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
    $digits = fn ($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
    $showDate = fn ($date) => $date
        ? $digits(\Illuminate\Support\Carbon::parse($date)->locale($isEn ? 'en' : 'bn')->translatedFormat($isEn ? 'd M Y' : 'j F Y'))
        : '—';
    $text = $isEn ? [
        'sl' => 'SL', 'requisition' => 'Req. No. / Date', 'department' => 'Section/Department', 'product' => 'Product Details',
        'requested' => 'Requested', 'received' => 'Received', 'no_department' => 'General / No Dept', 'total' => 'Grand Total:',
    ] : [
        'sl' => 'ক্রম', 'requisition' => 'রিকুইজিশন নং / তারিখ', 'department' => 'সেকশন/ডিপার্টমেন্ট', 'product' => 'পণ্যের বিবরণ',
        'requested' => 'অনুরোধকৃত', 'received' => 'প্রাপ্ত', 'no_department' => 'সাধারণ / সেকশন নেই', 'total' => 'সর্বমোট:',
    ];
@endphp
<table id="consumption-table" class="table" style="width:100%;">
    <thead>
        <tr>
            <th>{{ $text['sl'] }}</th>
            <th>{{ $text['requisition'] }}</th>
            <th>{{ $text['department'] }}</th>
            <th>{{ $text['product'] }}</th>
            <th class="text-center">{{ $text['requested'] }}</th>
            <th class="text-center">{{ $text['received'] }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($reportData as $row)
            <tr>
                <td class="text-center">{{ $digits($loop->iteration) }}</td>
                <td data-order="{{ $row->issued_date }}">
                    <strong>#{{ $digits($row->requisition_id) }}</strong><br>
                    <small class="text-muted">{{ $showDate($row->issued_date) }}</small>
                </td>
                <td class="font-weight-bold">{{ $row->department_name ?? $text['no_department'] }}</td>
                <td>
                    {{ $row->product_name }} <br>
                    <small class="text-muted">{{ $row->category_name }}</small>
                </td>

                {{-- Requested Quantity --}}
                <td class="text-center" data-order="{{ (float) $row->requested_quantity }}">
                    {{ $digits($row->requested_quantity) }}
                </td>

                {{-- Received Quantity (Highlighted red if less than requested) --}}
                <td class="text-center font-weight-bold {{ $row->received_quantity < $row->requested_quantity ? 'text-danger' : 'text-success' }}"
                    data-order="{{ (float) $row->received_quantity }}">
                    {{ $digits($row->received_quantity) }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr class="bg-light font-weight-bold">
            <td colspan="4" class="text-right">{{ $text['total'] }}</td>
            <td class="text-center">{{ $digits($reportData->sum('requested_quantity')) }}</td>
            <td class="text-center">{{ $digits($reportData->sum('received_quantity')) }}</td>
        </tr>
    </tfoot>
</table>
