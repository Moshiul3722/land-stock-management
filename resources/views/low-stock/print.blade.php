@php
    $isPreview = request()->boolean('preview');
    $isBn = app()->getLocale() === 'bn';
    $digits = fn ($value) => \App\Support\LowStock::digits($value);
    $now = now()->locale($isBn ? 'bn' : 'en');
    $t = $isBn ? [
        'gov' => 'গণপ্রজাতন্ত্রী বাংলাদেশ সরকার', 'ministry' => 'ভূমি মন্ত্রণালয়',
        'title' => 'স্টক কম মালামালের তালিকা',
        'hint' => 'যেসব মালামালের স্টক ' . $digits(\App\Support\LowStock::LIMIT) . '-এর কম',
        'date' => 'তারিখ', 'total' => 'মোট', 'items' => 'টি মালামাল',
        'serial' => 'ক্রমিক', 'product' => 'মালামাল', 'stock' => 'বর্তমান স্টক', 'status' => 'অবস্থা',
        'out' => 'শেষ', 'low' => 'কম', 'none' => 'এখন কোনও মালামালের স্টক কম নেই।',
        'print' => 'প্রিন্ট করুন', 'close' => 'বন্ধ করুন', 'page' => 'পৃষ্ঠা',
    ] : [
        'gov' => 'Government of the People\'s Republic of Bangladesh', 'ministry' => 'Ministry of Land',
        'title' => 'Low-stock products',
        'hint' => 'Products with less than ' . \App\Support\LowStock::LIMIT . ' in stock',
        'date' => 'Date', 'total' => 'Total', 'items' => ' products',
        'serial' => 'No.', 'product' => 'Product', 'stock' => 'Current stock', 'status' => 'Status',
        'out' => 'Out', 'low' => 'Low', 'none' => 'No product is low on stock now.',
        'print' => 'Print', 'close' => 'Close', 'page' => 'Page',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $isBn ? 'bn' : 'en' }}" @if ($isPreview) class="is-preview-page" @endif>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $t['title'] }}</title>
    <style>
        @font-face {
            font-family: 'Nikosh';
            src: local('Nikosh'), local('NikoshBAN'), url('{{ asset('fonts/Nikosh.ttf') }}') format('truetype');
            font-display: block;
        }

        @font-face {
            font-family: 'Nikosh';
            src: local('Nikosh'), local('NikoshBAN'), url('{{ asset('fonts/Nikosh.ttf') }}') format('truetype');
            unicode-range: U+0980-09FF, U+200C-200D, U+25CC;
            size-adjust: 125%;
            font-display: block;
        }

        * { box-sizing: border-box; }

        html, body { background: #fff; }

        body {
            margin: 0;
            color: #000;
            font-family: 'Nikosh', 'NikoshBAN', 'Kalpurush', sans-serif;
            font-size: 16px;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .sheet {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 14mm 14mm 16mm;
            background: #fff;
        }

        .head { display: flex; align-items: center; gap: 12px; padding-bottom: 8px; border-bottom: 2px solid #000; }
        .head img { width: 64px; height: auto; }
        .head .lines { flex: 1; text-align: center; }
        .head .l1 { font-size: 18px; }
        .head .l2 { font-size: 21px; font-weight: bold; }
        .head .spacer { width: 64px; }

        .title { margin: 6mm 0 1mm; font-size: 22px; font-weight: bold; text-align: center; }
        .meta { display: flex; justify-content: space-between; gap: 12px; margin-bottom: 4mm; font-size: 15px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 6px 8px; border: 1px solid #000; }
        th { background: rgba(0, 0, 0, 0.06); font-weight: bold; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; page-break-inside: avoid; }
        .c { text-align: center; }
        .nowrap { white-space: nowrap; }
        .out { font-weight: bold; }
        .none { padding: 12mm 0; text-align: center; }

        /* Preview: the list on a grey page with a bar to print or close */
        html.is-preview-page, body.is-preview { background: #e7e9e8; }
        body.is-preview { min-height: 100vh; padding-bottom: 1px; }
        body.is-preview .sheet { margin: 16px auto 24px; box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.45); }
        .bar { display: none; }
        body.is-preview .bar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px;
            background: #1a3528;
            font-family: Arial, sans-serif;
        }
        .bar button { min-height: 36px; padding: 0 16px; border: 1px solid transparent; border-radius: 8px; font: inherit; font-weight: 600; cursor: pointer; }
        .bar .print { background: #2f7452; color: #fff; }
        .bar .close { border-color: #cbd5e1; background: #fff; color: #334155; }
        .bar .size { margin-left: 8px; color: #cbd5e1; font-size: 13px; }

        @media screen and (max-width: 640px) {
            .bar .size { display: none; }
        }

        @page { size: A4; margin: 12mm 12mm 14mm; }

        @media print {
            html, html.is-preview-page, body, body.is-preview { background: #fff !important; }
            .bar { display: none !important; }
            /* On paper the page margins do the spacing; the list flows over as many pages as needed */
            .sheet, body.is-preview .sheet { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; zoom: 1 !important; }
        }
    </style>
</head>

<body @if ($isPreview) class="is-preview" @else onload="(document.fonts ? document.fonts.ready : Promise.resolve()).then(function () { window.print(); })" @endif>
    <script>
        (function () {
            var ua = navigator.userAgent;
            if (/Safari\//.test(ua) && !/Chrome|Chromium|CriOS|Edg|OPR|FxiOS|Android/.test(ua)) {
                document.documentElement.classList.add('is-safari');
            }
        })();

        // "Print" in the preview. Safari blanks the preview behind its print panel, so there a copy
        // of the sheet is printed from a hidden frame; other browsers print the page itself.
        function listPrint() {
            var sheet = document.getElementById('sheet');
            if (!document.documentElement.classList.contains('is-safari') || !sheet) {
                window.print();
                return;
            }
            var old = document.getElementById('list-print-frame');
            if (old) old.parentNode.removeChild(old);
            var frame = document.createElement('iframe');
            frame.id = 'list-print-frame';
            frame.setAttribute('aria-hidden', 'true');
            frame.style.cssText = 'position:absolute;left:-10000px;top:0;width:210mm;height:297mm;border:0;';
            document.body.appendChild(frame);
            var copy = sheet.cloneNode(true);
            copy.style.zoom = '';
            var doc = frame.contentWindow.document;
            doc.open();
            doc.write('<!DOCTYPE html><html class="is-safari"><' + 'head>' + document.head.innerHTML +
                '</' + 'head><' + 'body>' + copy.outerHTML + '</' + 'body></' + 'html>');
            doc.close();
            var printed = false;
            function go() {
                if (printed) return;
                printed = true;
                var win = frame.contentWindow;
                (win.document.fonts ? win.document.fonts.ready : Promise.resolve()).then(function () {
                    win.focus();
                    win.print();
                });
            }
            frame.onload = go;
            setTimeout(go, 600);
        }
    </script>

    @if ($isPreview)
        <div class="bar">
            <button type="button" class="print" onclick="listPrint()">{{ $t['print'] }}</button>
            <button type="button" class="close" onclick="window.close()">{{ $t['close'] }}</button>
            <span class="size">A4 · 210 × 297 mm</span>
        </div>
    @endif

    <div class="sheet" id="sheet">
        <div class="head">
            <img src="{{ asset('theme/assets/img/gov.png') }}" alt="">
            <div class="lines">
                <div class="l1">{{ $t['gov'] }}</div>
                <div class="l2">{{ $t['ministry'] }}</div>
            </div>
            <div class="spacer"></div>
        </div>

        <div class="title">{{ $t['title'] }}</div>
        <div class="meta">
            <span>{{ $t['hint'] }} · {{ $t['total'] }}: {{ $digits($items->count()) }}{{ $t['items'] }}</span>
            <span class="nowrap">{{ $t['date'] }}: {{ $digits($now->translatedFormat($isBn ? 'j F Y, g:i A' : 'd M Y, g:i A')) }}</span>
        </div>

        @if ($items->isEmpty())
            <p class="none">{{ $t['none'] }}</p>
        @else
            <table>
                <thead>
                    <tr>
                        <th class="c" style="width: 14mm;">{{ $t['serial'] }}</th>
                        <th>{{ $t['product'] }}</th>
                        <th class="c" style="width: 38mm;">{{ $t['stock'] }}</th>
                        <th class="c" style="width: 26mm;">{{ $t['status'] }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td class="c">{{ $digits($loop->iteration) }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td class="c nowrap">{{ trim($digits($item['total']) . ' ' . $item['unit']) }}</td>
                            <td class="c {{ $item['out'] ? 'out' : '' }}">{{ $item['out'] ? $t['out'] : $t['low'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    @if ($isPreview)
        <script>
            // On a narrow screen the A4 sheet is scaled down to fit the width (on screen only)
            (function () {
                function fit() {
                    var sheet = document.getElementById('sheet');
                    var room = document.documentElement.clientWidth - 16;
                    var full = sheet.offsetWidth / (parseFloat(sheet.style.zoom) || 1);
                    sheet.style.zoom = room < full ? (room / full).toFixed(3) : '';
                }
                fit();
                window.addEventListener('resize', fit);
            })();
        </script>
    @endif
</body>

</html>
