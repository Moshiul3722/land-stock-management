<!DOCTYPE html>
<html lang="bn">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>লট {{ $lot->lot_number }}</title>
    <style>
        body {
            font-family: 'SolaimanLipi', Arial, sans-serif;
            font-size: 14px;
            color: #000;
            padding: 20px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }

        .logo { width: 96px; height: auto; }
        .title { font-size: 20px; font-weight: bold; }
        .subtitle { font-size: 14px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .form-title {
            display: inline-block;
            margin: 6px 0 12px;
            padding: 5px 15px;
            border: 1px solid #000;
            font-size: 16px;
            font-weight: bold;
        }

        .info-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .info-table td { padding: 6px 8px; border: 1px solid #000; vertical-align: top; }
        .info-label { display: block; font-size: 12px; color: #333; }
        .info-value { font-weight: bold; }

        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 7px 8px; text-align: left; }
        .items-table th { background-color: #f0f0f0; }
        .items-table th.text-center, .items-table td.text-center { text-align: center; }
        .items-table tr { break-inside: avoid; }
        .item-note { display: block; font-size: 12px; color: #333; }

        .remarks { margin-top: 4px; }

        /* The approving officer's signature block, at the right (as on the requisition slip) */
        .signatures { margin-top: 15px; width: 100%; }
        .signatures::after { content: ""; display: block; clear: both; }
        .signature-box { float: right; width: 34%; text-align: center; }
        .signature-fill { display: flex; flex-direction: column; justify-content: flex-end; align-items: center; min-height: 8em; padding-bottom: 4px; line-height: 1.35; }
        .signature-image { max-width: 100%; max-height: 48px; margin-bottom: 2px; }
        .signature-name { font-weight: bold; }
        .signature-line { border-top: 1px solid #000; padding-top: 5px; }

        /* Print preview (?preview=1): the page on an A4 sheet, with a bar to print or close */
        body.is-preview { padding: 0; background: #e5e7eb; }
        .preview-bar { display: none; }
        body.is-preview .preview-bar { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: #0f1f47; }
        body.is-preview .preview-bar button { min-height: 36px; padding: 0 16px; border: 1px solid transparent; border-radius: 8px; font: inherit; font-weight: 600; cursor: pointer; }
        body.is-preview .preview-bar .preview-print { background: #1d65db; color: #fff; }
        body.is-preview .preview-bar .preview-close { border-color: #cbd5e1; background: #fff; color: #334155; }
        body.is-preview .preview-bar .preview-size { margin-left: 8px; color: #cbd5e1; font-size: 13px; }
/* Before the pages are built, the content waits out of sight */
        body.is-preview .preview-sheet { visibility: hidden; position: absolute; left: 0; top: 0; box-sizing: border-box; width: 210mm; padding: 10mm; }

        /* A4 pages: 210 x 297 mm each, the same on screen and on paper */
        .a4-pages { margin: 16px auto 24px; width: 210mm; }
        .a4-sheet { position: relative; box-sizing: border-box; width: 210mm; height: 297mm; margin: 0 0 16px; padding: 10mm 10mm 16mm; overflow: hidden; background: #fff; box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.45); }
        .a4-foot { position: absolute; right: 10mm; bottom: 6mm; left: 10mm; height: 6mm; font-size: 12px; line-height: 6mm; text-align: right; }

        @media screen and (max-width: 640px) {
            body.is-preview .preview-bar .preview-size { display: none; }
        }

        @media print {
            body, body.is-preview { padding: 0; background: #fff; -webkit-print-color-adjust: exact; }
            body.is-preview .preview-bar { display: none; }

@if (request()->boolean('preview'))
            /* Preview: each A4 page is printed as it is shown, with its own page number */
            @page { size: A4; margin: 0; }
            .a4-pages { width: 210mm; margin: 0; zoom: 1 !important; }
            .a4-sheet { margin: 0; box-shadow: none; break-after: page; }
            .a4-sheet:last-child { break-after: auto; }
@else
            /* Our own page footer replaces the browser's date, title and address lines:
               only the page number, in Bangla digits. */
            @page {
                size: A4;
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
        }
    </style>
</head>

@php
    // ?preview=1: show the page on an A4 sheet and wait for the user to press "print"
    $isPreview = request()->boolean('preview');
    $bnNum = fn ($value) => strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    $bnDate = fn ($date) => $bnNum(\Illuminate\Support\Carbon::parse($date)->locale('bn')->translatedFormat('j F Y'));
    $statusKey = 'lot.manage.status.' . $lot->status;
    $statusLabel = trans($statusKey, [], 'bn');
    $statusLabel = $statusLabel === $statusKey ? ucfirst((string) $lot->status) : $statusLabel;
    $hasBrands = $lot->lotDetails->contains(fn ($detail) => filled($detail->brand));
@endphp
<body @if ($isPreview) class="is-preview" @else onload="window.print()" @endif>

    @if ($isPreview)
        <div class="preview-bar">
            <button type="button" class="preview-print" onclick="window.print()">প্রিন্ট করুন</button>
            <button type="button" class="preview-close" onclick="window.close()">বন্ধ করুন</button>
            <span class="preview-size">A4 · 210 × 297 mm</span>
        </div>
    @endif

    <div class="preview-sheet">

        <table class="header-table" data-block>
            <tr>
                <td width="20%" class="text-center">
                    <img src="{{ asset('theme/assets/img/gov.png') }}" class="logo" alt="">
                </td>
                <td width="56%" class="text-center">
                    <div class="title">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</div>
                    <div class="subtitle">ভূমি মন্ত্রণালয়</div>
                    <div class="subtitle">বাংলাদেশ সচিবালয়, ঢাকা</div>
                </td>
                <td width="24%" class="text-right" style="white-space: nowrap;">
                    তারিখঃ {{ $bnDate(now()) }}
                </td>
            </tr>
        </table>

        <div class="text-center" data-block>
            <div class="form-title">স্টোরকৃত মালামালের বিবরণী</div>
        </div>

        <table class="info-table" data-block>
            <tr>
                <td width="25%"><span class="info-label">লট নম্বর</span><span class="info-value">{{ $lot->lot_number }}</span></td>
                <td width="25%"><span class="info-label">এন্ট্রি তারিখ</span><span class="info-value">{{ $bnDate($lot->entry_date) }}</span></td>
                <td width="25%"><span class="info-label">অর্থ বছর</span><span class="info-value">{{ $lot->fiscalYear ? $bnNum($lot->fiscalYear->name) : '—' }}</span></td>
                <td width="25%"><span class="info-label">পণ্য ক্রয়ের ধরণ</span><span class="info-value">{{ $lot->purchaseType->name ?? '—' }}</span></td>
            </tr>
            <tr>
                <td colspan="4"><span class="info-label">অবস্থা</span><span class="info-value">{{ $statusLabel }}</span></td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th width="9%" class="text-center">ক্রমিক নং</th>
                    <th width="41%">আইটেমের বিবরণ</th>
                    <th width="25%">ক্যাটাগরি</th>
                    <th width="25%" class="text-center">স্টোরকৃত মালামালের পরিমাণ</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lot->lotDetails as $detail)
                    @php $note = collect([$hasBrands ? $detail->brand : null, $detail->specification])->filter()->join(' · '); @endphp
                    <tr>
                        <td class="text-center">{{ $bnNum($loop->iteration) }}</td>
                        <td>
                            {{ $detail->product->name ?? '—' }}
                            @if ($note !== '')
                                <span class="item-note">{{ $note }}</span>
                            @endif
                        </td>
                        <td>{{ $detail->product->category->name ?? '—' }}</td>
                        <td class="text-center">
                            <strong>{{ $bnNum($detail->quantity) }}</strong>
                            @if ($detail->product && $detail->product->product_unit && ! is_numeric($detail->product->product_unit))
                                {{ $detail->product->unit_label }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($lot->remarks)
            <div class="remarks" data-tail><strong>মন্তব্যঃ</strong> {{ $lot->remarks }}</div>
        @endif

        {{-- Signature: filled in automatically once the lot has been approved --}}
        <div class="signatures" data-tail>
            <div class="signature-box">
                <div class="signature-fill">
                    @if ($approver)
                        @if ($uri = $approver->signatureDataUri())
                            <img src="{{ $uri }}" class="signature-image" alt="">
                        @endif
                        <span class="signature-name">{{ trim((string) $approver->name_bn) !== '' ? $approver->name_bn : $approver->name }}</span>
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
                        body = document.createElement('div');
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
                    // What follows the table (remarks, signature): on the last page, or a new one if it does not fit
                    Array.prototype.forEach.call(source.querySelectorAll('[data-tail]'), function (tail) {
                        body.appendChild(tail);
                        if (overflows()) { newSheet(); body.appendChild(tail); }
                    });
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
