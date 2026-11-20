@php
    $isPreview = request()->boolean('preview');
    $bn = fn ($value) => strtr((string) $value, ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯']);
    $date = fn ($d) => $d ? $bn($d->format('d/m/Y')) : '';
    $time = function ($value) use ($bn) {
        if (! $value) {
            return '';
        }
        $at = \Illuminate\Support\Carbon::createFromFormat('H:i', substr((string) $value, 0, 5));

        return $bn($at->format('g:i')) . ' ' . ($at->hour < 12 ? 'পূর্বাহ্ণ' : 'অপরাহ্ণ');
    };
    $number = fn ($value) => $value !== null ? $bn(rtrim(rtrim((string) $value, '0'), '.')) : '';
    $official = $req->purpose === 'official';
    $applicantSign = $req->user?->signatureDataUri();
    $approved = $req->status === 'approved';
    $approverSign = $approved ? $req->decider?->signatureDataUri() : null;
    $approverName = $req->decider ? ($req->decider->name_bn ?: $req->decider->name) : '';
@endphp
<!DOCTYPE html>
<html lang="bn" @if ($isPreview) class="is-preview-page" @endif>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>গাড়ি রিকুইজিশন ফরম #{{ $bn($req->id) }}</title>
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

        @font-face {
            font-family: 'Kalpurush';
            src: local('Kalpurush'), url('{{ asset('fonts/kalpurush.ttf') }}') format('truetype');
            font-display: block;
        }

        * { box-sizing: border-box; }

        html, body { background: #fff; }

        body {
            margin: 0;
            color: #000;
            font-family: 'Nikosh', 'NikoshBAN', 'Kalpurush', sans-serif;
            font-size: 17px;
            line-height: 1.45;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* The form on an A4 sheet */
        .sheet {
            position: relative;
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            padding: 14mm 14mm 12mm;
            overflow: hidden;
            background: #fff;
        }

        .mark {
            position: absolute;
            top: 50%;
            left: 50%;
            z-index: 0;
            width: 110mm;
            transform: translate(-50%, -50%);
            opacity: 0.07;
            pointer-events: none;
        }

        .body { position: relative; z-index: 1; }

        .head { text-align: center; line-height: 1.35; }
        .head .l1 { font-size: 19px; }
        .head .l2 { font-size: 22px; font-weight: bold; }
        .head .l3, .head .l4 { font-size: 17px; }

        .title-row { position: relative; margin: 6mm 0 5mm; text-align: center; }
        .title {
            display: inline-block;
            padding: 3px 22px;
            border: 1.5px solid #000;
            border-radius: 6px;
            font-size: 24px;
            font-weight: bold;
        }
        .serial { position: absolute; right: 0; bottom: 0; font-size: 16px; }

        table.grid { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.grid th, table.grid td { border: 1px solid #000; padding: 6px 7px; vertical-align: top; }
        table.grid th { font-weight: bold; text-align: center; vertical-align: middle; background: rgba(0, 0, 0, 0.05); }
        table.grid td { word-wrap: break-word; overflow-wrap: anywhere; }

        .main td { height: 48mm; }
        .tick {
            display: inline-block;
            width: 22px;
            height: 22px;
            margin-left: 8px;
            border: 1.5px solid #000;
            line-height: 19px;
            text-align: center;
            font-family: 'Segoe UI Symbol', 'DejaVu Sans', Arial, sans-serif;
            font-size: 20px;
            font-weight: 900;
            vertical-align: middle;
        }
        .purpose-line { margin-bottom: 10px; white-space: nowrap; }
        .center { text-align: center; }
        .dots { display: inline-block; min-width: 26mm; border-bottom: 1px dotted #000; text-align: center; }

        .two td { height: 50mm; width: 50%; }
        .line { display: flex; gap: 6px; margin-bottom: 9px; }
        .line .k { flex: none; }
        .line .v { flex: 1; min-height: 1.4em; border-bottom: 1px dotted #000; }
        .sign { display: block; max-width: 65mm; max-height: 24mm; margin-top: 2px; }
        .sign-box { min-height: 26mm; }

        .note { margin: 5mm 0; font-size: 21px; font-weight: bold; text-align: center; }

        .bottom td { height: 40mm; text-align: center; vertical-align: middle; }
        .status { margin-top: 4mm; font-size: 16px; }
        .status.rejected { font-weight: bold; }

        /* Print preview: the sheet on a grey page with a bar to print or close */
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
        .bar button {
            min-height: 36px;
            padding: 0 16px;
            border: 1px solid transparent;
            border-radius: 8px;
            font: inherit;
            font-weight: 600;
            cursor: pointer;
        }
        .bar .print { background: #2f7452; color: #fff; }
        .bar .close { border-color: #cbd5e1; background: #fff; color: #334155; }
        .bar .size { margin-left: 8px; color: #cbd5e1; font-size: 13px; }

        /* Narrow screens: the A4 sheet is scaled down to fit (see the script below) */
        @media screen and (max-width: 640px) {
            .bar .size { display: none; }
        }

        @page { size: A4; margin: 0; }

        @media print {
            html, html.is-preview-page, body, body.is-preview { background: #fff !important; }
            .bar { display: none !important; }
            /* No fixed height on paper, so no browser (Safari included) spills onto a second page */
            .sheet, body.is-preview .sheet { min-height: 0; margin: 0; box-shadow: none; zoom: 1 !important; }
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
        function formPrint() {
            var sheet = document.getElementById('sheet');
            if (!document.documentElement.classList.contains('is-safari') || !sheet) {
                window.print();
                return;
            }
            var old = document.getElementById('form-print-frame');
            if (old) old.parentNode.removeChild(old);
            var frame = document.createElement('iframe');
            frame.id = 'form-print-frame';
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
            <button type="button" class="print" onclick="formPrint()">প্রিন্ট করুন</button>
            <button type="button" class="close" onclick="window.close()">বন্ধ করুন</button>
            <span class="size">A4 · 210 × 297 mm</span>
        </div>
    @endif

    <div class="sheet" id="sheet">
        <img class="mark" alt="" src="{{ asset('Government_Seal_of_Bangladesh.svg') }}">
        <div class="body">
            <div class="head">
                <div class="l1">গণপ্রজাতন্ত্রী বাংলাদেশ সরকার</div>
                <div class="l2">ভূমি মন্ত্রণালয়</div>
                <div class="l3">শাখা-১ (প্রশাসন)</div>
                <div class="l4">www.minland.gov.bd</div>
            </div>

            <div class="title-row">
                <span class="title">গাড়ি রিকুইজিশন ফরম</span>
                <span class="serial">ক্রমিক নং: {{ $bn($req->id) }}</span>
            </div>

            <table class="grid main">
                <colgroup><col style="width: 20%"><col style="width: 25%"><col style="width: 19%"><col style="width: 20%"><col style="width: 16%"></colgroup>
                <thead>
                    <tr>
                        <th>যে কারণে গাড়ি প্রয়োজন</th>
                        <th>সরকারী কাজের সংক্ষিপ্ত বিবরণ</th>
                        <th>গন্তব্য</th>
                        <th>গাড়ি চলাচলের জন্য আনুমানিক দূরত্ব</th>
                        <th>গাড়ি ফেরত প্রদানের সময়</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="purpose-line">ক. সরকারী <span class="tick">{{ $official ? '✔' : '' }}</span></div>
                            <div class="purpose-line">খ. ব্যক্তিগত <span class="tick">{{ $official ? '' : '✔' }}</span></div>
                        </td>
                        <td>{{ $req->official_details }}</td>
                        <td>{{ $req->destination }}</td>
                        <td class="center">
                            <span class="dots">{{ $number($req->distance_km) }}</span> কিঃমিঃ
                            <div style="margin-top: 4px;">(যাতায়াত)</div>
                        </td>
                        <td class="center">{{ $time($req->return_time) }}</td>
                    </tr>
                </tbody>
            </table>

            <table class="grid two" style="border-top: 0;">
                <tbody>
                    <tr>
                        <td style="border-top: 0;">
                            <div class="line"><span class="k">গাড়ি রিপোর্ট স্থান:</span><span class="v">{{ $req->report_place }}</span></div>
                            <div class="line"><span class="k">তারিখ:</span><span class="v">{{ $date($req->report_date) }}</span></div>
                            <div class="line"><span class="k">সময়:</span><span class="v">{{ $time($req->report_time) }}</span></div>
                        </td>
                        <td style="border-top: 0;">
                            <div class="line"><span class="k">আবেদনকারীর নাম:</span><span class="v">{{ $req->applicant_name }}</span></div>
                            <div class="line"><span class="k">পদবী:</span><span class="v">{{ $req->applicant_designation }}</span></div>
                            <div class="line"><span class="k">স্বাক্ষর ও তারিখ:</span>
                                <span class="v sign-box">
                                    @if ($applicantSign)<img class="sign" alt="" src="{{ $applicantSign }}">@endif
                                    {{ $date($req->created_at) }}
                                </span>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <p class="note">ব্যক্তিগত ব্যবহারের ক্ষেত্রে জ্বালানী ব্যবহারকারীকে প্রদান করিতে হইবে।</p>

            <table class="grid bottom">
                <thead>
                    <tr>
                        <th>চালকের নাম</th>
                        <th>অনুমোদনকারীর স্বাক্ষর</th>
                        <th>গাড়ির নম্বর</th>
                        <th>জ্বালানীর পরিমাণ</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $req->driver ? ($req->driver->name_bn ?: $req->driver->name) : '' }}</td>
                        <td>
                            @if ($approverSign)<img class="sign" style="margin: 0 auto;" alt="" src="{{ $approverSign }}">@endif
                            @if ($approved)
                                <div>{{ $approverName }}</div>
                                <div>{{ $req->decided_at ? $date($req->decided_at) : '' }}</div>
                            @endif
                        </td>
                        <td>{{ $req->vehicle?->vehicle_no }}</td>
                        <td>{{ $req->fuel_amount !== null ? trim($number($req->fuel_amount) . ' ' . $req->fuel_unit) : '' }}</td>
                    </tr>
                </tbody>
            </table>

            @if ($req->status === 'rejected')
                <p class="status rejected">প্রত্যাখ্যাত{{ $req->decision_note ? ' — ' . $req->decision_note : '' }}</p>
            @elseif ($approved && $req->decision_note)
                <p class="status">মন্তব্য: {{ $req->decision_note }}</p>
            @endif
        </div>
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
