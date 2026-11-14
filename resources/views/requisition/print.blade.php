<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requisition Slip #{{ $requisition->id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 20px;
        }

        .header-table {
            width: 100%;
            table-layout: fixed;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .qr-box {
            display: inline-block;
            text-align: center;
        }

        .qr-box small {
            display: block;
            margin-top: 6px;
        }

        /* Two right-aligned lines that never wrap (they grow to the left if they need room) */
        .date-lines {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            white-space: nowrap;
            line-height: 1.4;
        }

        .subject-line {
            font-size: 17px;
            font-weight: bold;
        }

        .logo {
            width: 96px;
            height: auto;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 14px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 6px;
        }

        .info-table td {
            padding: 2px 5px;
            vertical-align: top;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        .items-table th {
            /* The same light grey as before (#f0f0f0 on white), see-through for the watermark */
            background-color: rgba(0, 0, 0, 0.06);
        }

        .items-table tr {
            break-inside: avoid;
        }

        /* Every column except the description is centred */
        .items-table th.text-center,
        .items-table td.text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .signatures {
            margin-top: 15px;
            width: 100%;
        }

        .signature-box {
            width: 34%;
            float: left;
            text-align: center;
        }

        /* Name, designation and date, sitting on the signature line */
        .signature-fill {
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            min-height: 8em;
            padding-bottom: 4px;
            line-height: 1.35;
        }

        .signature-image {
            max-width: 100%;
            max-height: 48px;
            margin-bottom: 2px;
        }

        .signature-name {
            font-weight: bold;
        }

        .signature-line {
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .signatures::after {
            content: "";
            display: block;
            clear: both;
        }

        .department-name {
            margin-top: 4px;
            font-size: 16px;
            font-weight: bold;
        }

        .spacer {
            width: 5%;
            float: left;
        }

        /* Print preview (?preview=1): the slip on an A4 sheet, with a bar to print or close.
           Nothing here applies to the ordinary print page. */
        body.is-preview {
            padding: 0;
            background: #e5e7eb;
        }

        body.is-preview .preview-bar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: #0f1f47;
        }

        body.is-preview .preview-bar button {
            min-height: 36px;
            padding: 0 16px;
            border: 1px solid transparent;
            border-radius: 8px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }

        body.is-preview .preview-bar .preview-print {
            background: #1d65db;
            color: #fff;
        }

        body.is-preview .preview-bar .preview-close {
            border-color: #cbd5e1;
            background: #fff;
            color: #334155;
        }

        body.is-preview .preview-bar .preview-size {
            margin-left: 8px;
            color: #cbd5e1;
            font-size: 13px;
        }

        /* Before the pages are built, the content waits out of sight */
        body.is-preview .preview-sheet {
            visibility: hidden;
            position: absolute;
            left: 0;
            top: 0;
            box-sizing: border-box;
            width: 210mm;
            padding: 10mm;
        }

        /* A4 pages: 210 x 297 mm each, the same on screen and on paper */
        .a4-pages {
            width: 210mm;
            margin: 16px auto 24px;
        }

        .a4-sheet {
            position: relative;
            box-sizing: border-box;
            width: 210mm;
            height: 297mm;
            margin: 0 0 16px;
            padding: 10mm 10mm 16mm;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.45);
        }

        /* Watermark: the government seal, faint, in the middle of every page and behind the
           text. An image (not a background), so it is printed without any extra setting. */
        .a4-mark,
        .slip-mark {
            top: 50%;
            left: 50%;
            z-index: 0;
            width: 120mm;
            height: auto;
            transform: translate(-50%, -50%);
            opacity: 0.08;
            pointer-events: none;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .a4-mark {
            position: absolute;
        }

        /* Printed straight away (no preview): a fixed image is repeated on every printed page */
        .slip-mark {
            position: fixed;
        }

        .a4-body,
        body:not(.is-preview) .preview-sheet {
            position: relative;
            z-index: 1;
        }

        .a4-foot {
            z-index: 1;
            position: absolute;
            right: 10mm;
            bottom: 6mm;
            left: 10mm;
            height: 6mm;
            font-size: 12px;
            line-height: 6mm;
            text-align: right;
        }

        .preview-bar {
            display: none;
        }

        @media screen and (max-width: 640px) {
            body.is-preview .preview-bar .preview-size {
                display: none;
            }
        }

        /* Print Styles */
        @media print {
            body.is-preview {
                padding: 0;
                background: #fff;
            }

            body.is-preview .preview-bar {
                display: none;
            }

@if (request()->boolean('preview'))
            /* Preview: each A4 page is printed as it is shown, with its own page number */
            @page {
                size: A4;
                margin: 0;
            }

            .a4-pages {
                width: 210mm;
                margin: 0;
                zoom: 1 !important;
            }

            .a4-sheet {
                margin: 0;
                box-shadow: none;
                break-after: page;
            }

            .a4-sheet:last-child {
                break-after: auto;
            }
@else
            /* Our own page footer replaces the browser's date, title and address lines:
               only the page number, in Bangla digits. */
            @page {
                margin: 1cm 1cm 1.4cm;

                @top-left { content: ""; }
                @top-center { content: ""; }
                @top-right { content: ""; }
                @bottom-left { content: ""; }
                @bottom-center { content: ""; }

                @bottom-right {
                    content: "পৃষ্ঠা " counter(page, bengali) "/" counter(pages, bengali);
                    font-family: inherit;
                    font-size: 12px;
                    color: #000;
                }
            }
@endif

            body {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>

@php
    // ?preview=1: show the slip on an A4 sheet and wait for the user to press "print"
    $isPreview = request()->boolean('preview');
    $previewBn = app()->getLocale() === 'bn';
@endphp
<body @if ($isPreview) class="is-preview" @else onload="window.print()" @endif>

    @if ($isPreview)
        <div class="preview-bar">
            <button type="button" class="preview-print" onclick="window.print()">{{ $previewBn ? 'প্রিন্ট করুন' : 'Print' }}</button>
            <button type="button" class="preview-close" onclick="window.close()">{{ $previewBn ? 'বন্ধ করুন' : 'Close' }}</button>
            <span class="preview-size">A4 · 210 × 297 mm</span>
        </div>
    @endif

    @unless ($isPreview)
        <img class="slip-mark" alt="" src="{{ asset('Government_Seal_of_Bangladesh.svg') }}">
    @endunless

    <div class="preview-sheet">

    @php
        // The requester's department, in Bangla when it has a Bangla name
        $department = $requisition->requestedBy->department;
        $departmentName = trim((string) ($department->name_bn ?? '')) !== '' ? $department->name_bn : (string) ($department->name ?? '');
        // Numbers in Bangla digits
        $bnNum = fn ($value) => strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
        // A date in Bangla: ১৩ নভেম্বর ২০২৬
        $bnDate = fn ($date) => $bnNum(\Illuminate\Support\Carbon::parse($date)->locale('bn')->translatedFormat('j F Y'));
        // The same day in the Bangla calendar (revised 2019 calendar): ২৩ আষাঢ়, ১৪৩৩ বঙ্গাব্দ
        $bongabdo = function ($date) use ($bnNum) {
            $date = \Illuminate\Support\Carbon::parse($date)->startOfDay();
            $year = $date->year;
            $start = \Illuminate\Support\Carbon::create($year, 4, 14)->startOfDay();
            if ($date->lt($start)) {
                $year -= 1;
                $start = \Illuminate\Support\Carbon::create($year, 4, 14)->startOfDay();
            }
            // Falgun falls in February / March of the next Gregorian year
            $leap = \Illuminate\Support\Carbon::create($year + 1, 1, 1)->isLeapYear();
            $lengths = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, $leap ? 30 : 29, 30];
            $names = ['বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র'];
            $day = (int) round($start->diffInDays($date));
            $month = 0;
            while ($month < 11 && $day >= $lengths[$month]) {
                $day -= $lengths[$month];
                $month++;
            }

            return $bnNum($day + 1) . ' ' . $names[$month] . ', ' . $bnNum($year - 593) . ' বঙ্গাব্দ';
        };
        // A person's name as printed in a signature block
        $signName = fn ($user) => $user ? (trim((string) $user->name_bn) !== '' ? $user->name_bn : $user->name) : '';
    @endphp

    {{-- 1. Header --}}
    <table class="header-table" data-block>
        <tr>
            {{-- Equal side columns (logo, QR code) keep the titles in the middle of the page --}}
            <td width="20%" class="text-center">
                <img src="{{ asset('theme/assets/img/gov.png') }}" class="logo">
            </td>
            <td width="60%" class="text-center">
                <div class="title">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</div>
                <div class="subtitle">ভূমি মন্ত্রনালয়</div>
                <div class="subtitle">বাংলাদেশ সচিবালয়, ঢাকা</div>
                @if ($departmentName !== '')
                    <div class="department-name">{{ $departmentName }}</div>
                @endif
            </td>
            <td width="20%" class="text-right">
                {{-- 2. QR Code (Contains link to view this requisition) --}}
                {{-- NOTE: We use route('requisitions.show') so scanning it opens the details --}}
                {{-- The code and its caption sit together at the right edge --}}
                <div class="qr-box">
                    {!! DNS2D::getBarcodeHTML(route('requisitions.show', $requisition->id), 'QRCODE', 3, 3) !!}
                    <small>স্ক্যান করুন</small>
                </div>
            </td>
        </tr>
        <tr>
            <td colspan="2" class="subject-line">
                বিষয়ঃ মনিহারী দ্রব্যাদি/বিবিধ মালামালের অধিযাচন ফরম।
            </td>
            <td class="text-right">
                {{-- The date, with the same day in the Bangla calendar under it --}}
                <div class="date-lines">
                    <span>তারিখঃ {{ $bnDate($requisition->requisition_date) }}</span>
                    <span>{{ $bongabdo($requisition->requisition_date) }}</span>
                </div>
            </td>
        </tr>
    </table>

    <div class="text-center" style="margin-bottom: 4px;" data-block>
        <h3 style="border: 1px solid #000; display: inline-block; margin: 12px 0 0; padding: 5px 15px; text-transform: uppercase;">
            মালামাল অধিযাচন ফরম
        </h3>
    </div>

    {{-- 3. Info Section --}}
    <table class="info-table" data-block>
        <tr>
            <td width="50%"></td>
            <td width="50%" class="text-right">
                <strong>Requisition ID:</strong> #{{ $requisition->id }}
            </td>
        </tr>
    </table>

    {{-- 4. Items Table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th width="8%" class="text-center">ক্রমিক নং</th>
                <th width="40%">মালামালের বিবরণ</th>
                <th width="32%" class="text-center">পূর্বে গ্রহণের তারিখ ও পরিমাণ</th>
                <th width="20%" class="text-center">অনুমোদিত পরিমাণ</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($requisition->items as $item)
                @php $last = $lastReceipts->get($item->product_id); @endphp
                <tr>
                    <td class="text-center">{{ $bnNum($loop->iteration) }}</td>
                    <td>{{ $item->product->name }}</td>
                    <td class="text-center">
                        @if ($last)
                            {{ $bnDate($last['date_iso']) }}
                            ({{ $bnNum($last['quantity']) }} {{ $item->product->unit_label }})
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-center">
                        <strong>{{ $bnNum($item->quantity) }}</strong> {{ $item->product->unit_label }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 5. Footer / Signatures: filled in automatically --}}
    <div class="signatures" data-tail>
        <div class="signature-box" style="float: left;">
            <div class="signature-fill">
                @if ($uri = $requisition->requestedBy->signatureDataUri())
                    <img src="{{ $uri }}" class="signature-image" alt="">
                @endif
                <span class="signature-name">{{ $signName($requisition->requestedBy) }}</span>
                <span>তারিখঃ {{ $bnDate($requisition->requisition_date) }}</span>
            </div>
            <div class="signature-line">
                অধিযাচনকারী কর্মকর্তা<br>
                <small>(স্বাক্ষর এবং তারিখ)</small>
            </div>
        </div>

        <div class="signature-box" style="float: right;">
            <div class="signature-fill">
                @if ($approver)
                    @if ($uri = $approver->signatureDataUri())
                        <img src="{{ $uri }}" class="signature-image" alt="">
                    @endif
                    <span class="signature-name">{{ $signName($approver) }}</span>
                    {{-- Designation from the profile; the role name until one is entered --}}
                    @if (trim((string) $approver->designation) !== '')
                        <span>{{ $approver->designation }}</span>
                    @elseif ($approverRole)
                        <span>{{ $approverRole->role_name_bn ?: $approverRole->role_name_en }}</span>
                    @endif
                    @if ($approvedAt)
                        <span>তারিখঃ {{ $bnDate($approvedAt) }}</span>
                    @endif
                @endif
            </div>
            <div class="signature-line">
                অনুমোদনকারী কর্মকর্তা<br>
                <small>(স্বাক্ষর, পদবি এবং তারিখ)</small>
            </div>
        </div>
    </div>

    </div>

    @if ($isPreview)
        <div class="a4-pages" id="a4-pages"></div>
        <script>
            // Deals the content onto A4 pages (210 x 297 mm): the heading on the first page, the
            // table's header row repeated on every page, and "page x/y" at the bottom of each.
            (function () {
                var bn = function (n) { return String(n).replace(/[0-9]/g, function (d) { return '০১২৩৪৫৬৭৮৯'[d]; }); };

                function build() {
                    var source = document.querySelector('.preview-sheet');
                    var pages = document.getElementById('a4-pages');
                    var table = source.querySelector('.items-table');
                    var rows = Array.prototype.slice.call(table.tBodies[0].rows);
                    var sheet, body, tbody;

                    function newSheet() {
                        sheet = document.createElement('div');
                        sheet.className = 'a4-sheet';
                        var mark = document.createElement('img');
                        mark.className = 'a4-mark';
                        mark.alt = '';
                        mark.src = @json(asset('Government_Seal_of_Bangladesh.svg'));
                        sheet.appendChild(mark);
                        body = document.createElement('div');
                        body.className = 'a4-body';
                        sheet.appendChild(body);
                        var foot = document.createElement('div');
                        foot.className = 'a4-foot';
                        sheet.appendChild(foot);
                        pages.appendChild(sheet);
                        tbody = null;
                    }
                    // Room for content: the page without its padding (the footer sits in the bottom padding)
                    function overflows() {
                        var style = getComputedStyle(sheet);
                        var room = sheet.clientHeight - parseFloat(style.paddingTop) - parseFloat(style.paddingBottom);
                        return body.scrollHeight > room + 1;
                    }
                    function newTable() {
                        var copy = table.cloneNode(false);
                        copy.appendChild(table.tHead.cloneNode(true));
                        tbody = document.createElement('tbody');
                        copy.appendChild(tbody);
                        body.appendChild(copy);
                    }

                    newSheet();
                    Array.prototype.forEach.call(source.querySelectorAll('[data-block]'), function (block) { body.appendChild(block); });
                    newTable();
                    rows.forEach(function (row) {
                        tbody.appendChild(row);
                        if (overflows() && tbody.rows.length > 1) {
                            newSheet();
                            newTable();
                            tbody.appendChild(row);
                        }
                    });
                    var tail = source.querySelector('[data-tail]');
                    if (tail) {
                        body.appendChild(tail);
                        if (overflows()) { newSheet(); body.appendChild(tail); }
                    }
                    source.remove();

                    var sheets = pages.querySelectorAll('.a4-sheet');
                    Array.prototype.forEach.call(sheets, function (page, index) {
                        page.querySelector('.a4-foot').textContent = 'পৃষ্ঠা ' + bn(index + 1) + '/' + bn(sheets.length);
                    });
                    fit();
                }

                // Narrow screens: the whole A4 page is shown smaller instead of being cut
                function fit() {
                    var pages = document.getElementById('a4-pages');
                    pages.style.zoom = 1;
                    var scale = Math.min(1, (document.documentElement.clientWidth - 16) / pages.offsetWidth);
                    pages.style.zoom = scale;
                }

                window.addEventListener('load', build);
                window.addEventListener('resize', fit);
            })();
        </script>
    @endif
</body>

</html>
