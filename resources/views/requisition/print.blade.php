<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisition Slip #{{ $requisition->id }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 14px; margin: 0; padding: 20px; }
        .header-table { width: 100%; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .logo { width: 60px; height: auto; }
        .title { font-size: 20px; font-weight: bold; text-transform: uppercase; }
        .subtitle { font-size: 14px; }
        
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; vertical-align: top; }
        
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .items-table th { background-color: #f0f0f0; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .signatures { margin-top: 50px; width: 100%; }
        .signature-box { width: 30%; float: left; text-align: center; border-top: 1px solid #000; padding-top: 5px; }
        .spacer { width: 5%; float: left; }
        
        /* Print Styles */
        @media print {
            @page { margin: 1cm; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body onload="window.print()">

    {{-- 1. Header --}}
    <table class="header-table">
        <tr>
            <td width="15%" class="text-center">
                <img src="{{ asset('theme/assets/img/gov.png') }}" class="logo">
            </td>
            <td width="60%" class="text-center">
                <div class="title">Ministry of Land</div>
                <div class="subtitle">Inventory Management System</div>
                <div class="subtitle">Bangladesh Secretariat, Dhaka</div>
            </td>
            <td width="25%" class="text-right">
                {{-- 2. QR Code (Contains link to view this requisition) --}}
                {{-- NOTE: We use route('requisitions.show') so scanning it opens the details --}}
                {!! DNS2D::getBarcodeHTML(route('requisitions.show', $requisition->id), 'QRCODE', 4, 4) !!}
                <small>Scan to View</small>
            </td>
        </tr>
    </table>

    <div class="text-center" style="margin-bottom: 20px;">
        <h3 style="border: 1px solid #000; display: inline-block; padding: 5px 15px; text-transform: uppercase;">
            Store Requisition Slip
        </h3>
    </div>

    {{-- 3. Info Section --}}
    <table class="info-table">
        <tr>
            <td width="50%">
                <strong>Requestor:</strong> {{ $requisition->requestedBy->name }} <br>
                <strong>Department:</strong> {{ $requisition->requestedBy->department->name ?? 'N/A' }} <br>
                <strong>Date:</strong> {{ $requisition->requisition_date->format('d M, Y') }}
            </td>
            <td width="50%" class="text-right">
                <strong>Requisition ID:</strong> #{{ $requisition->id }} <br>
                <strong>Status:</strong> {{ ucfirst($requisition->status) }}
            </td>
        </tr>
    </table>

    {{-- 4. Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th width="5%" class="text-center">SL</th>
                <th width="30%">Product</th>
                <th width="20%">Category</th>
                <th width="30%">Assigned Brand/Spec</th>
                <th width="15%" class="text-center">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($requisition->items as $item)
                <tr>
                    <td class="text-center">{{ $loop->iteration }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->category->name }}</td>
                    <td>
                        @if($item->lot)
                            {{ $item->lot->lotDetails->where('product_id', $item->product_id)->first()->brand ?? 'N/A' }} <br>
                            <small>{{ $item->lot->lotDetails->where('product_id', $item->product_id)->first()->specification ?? '' }}</small>
                        @else
                            <span style="color:red">Not Assigned</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <strong>{{ $item->quantity }}</strong>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 5. Footer / Signatures --}}
    <div class="signatures">
        <div class="signature-box" style="float: left;">
            Requested By<br>
            <small>(Signature & Date)</small>
        </div>
        
        <div class="spacer" style="width: 35%"></div> {{-- Spacer --}}

        <div class="signature-box" style="float: right;">
            Issued By<br>
            <small>(Signature & Date)</small>
        </div>
    </div>

</body>
</html>