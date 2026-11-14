@extends('layouts.app')

@push('page_css')
    {{-- The calendar date fields are Tailwind components --}}
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
@endpush

@section('content')
    <section class="content-header list-page">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('stock.current_stock_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.list') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content list-page">
        @php
            $isEn = app()->getLocale() === 'en';
            $bnDigits = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪', '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];
            $digits = fn ($value) => $isEn ? (string) $value : strtr((string) $value, $bnDigits);
            $text = $isEn ? [
                'list' => 'Stock Report List',
                'all_categories' => 'All Categories',
                'all_products' => 'All Products',
                'category_first' => 'Select Category First',
                'all_purchase_types' => 'All Purchase Types',
                'loading' => 'Loading...',
                'search' => 'Search by category, sub-category or product',
                'date_from' => 'Start Date',
                'date_to' => 'End Date',
                'pick_date' => 'Pick a date',
                'preview' => 'Print Preview',
                'product_category' => 'Product Category',
                'print_now' => 'Print',
                'close' => 'Close',
                'ministry' => 'Ministry of Land',
                'system' => 'Inventory Management System',
                'period' => 'Period',
                'period_all' => 'All dates',
                'from_start' => 'the beginning',
                'till_today' => 'today',
                'to' => 'to',
                'printed_on' => 'Printed on',
                'page' => 'Page',
                'verifier_signature' => "Report Verifier's Signature",
                'sign_name' => 'Name',
                'sign_designation' => 'Designation',
                'sign_date' => 'Date',
                'load_failed' => 'Failed to load stock report.',
                'products_failed' => 'Failed to load products.',
                'report_title' => 'Current Stock Report',
                'generated' => 'Generated',
                'no_match' => 'No match found',
                'type_to_search' => 'Type to search',
            ] : [
                'list' => 'স্টক রিপোর্টের তালিকা',
                'all_categories' => 'সকল ক্যাটাগরি',
                'all_products' => 'সকল প্রোডাক্ট',
                'category_first' => 'আগে ক্যাটাগরি বাছুন',
                'all_purchase_types' => 'সকল ক্রয়ের ধরণ',
                'loading' => 'লোড হচ্ছে...',
                'search' => 'ক্যাটাগরি, সাব-ক্যাটাগরি বা পণ্য দিয়ে খুঁজুন',
                'date_from' => 'শুরুর তারিখ',
                'date_to' => 'শেষের তারিখ',
                'pick_date' => 'তারিখ বাছুন',
                'preview' => 'প্রিন্ট প্রিভিউ',
                'product_category' => 'প্রোডাক্ট ক্যাটাগরি',
                'print_now' => 'প্রিন্ট করুন',
                'close' => 'বন্ধ করুন',
                'ministry' => 'ভূমি মন্ত্রণালয়',
                'system' => 'ইনভেন্টরি ম্যানেজমেন্ট সিস্টেম',
                'period' => 'সময়কাল',
                'period_all' => 'সকল তারিখ',
                'from_start' => 'শুরু',
                'till_today' => 'আজ',
                'to' => 'থেকে',
                'printed_on' => 'প্রিন্টের তারিখ',
                'page' => 'পৃষ্ঠা',
                'verifier_signature' => 'প্রতিবেদন যাচাইকারীর স্বাক্ষর',
                'sign_name' => 'নাম',
                'sign_designation' => 'পদবি',
                'sign_date' => 'তারিখ',
                'load_failed' => 'স্টক রিপোর্ট লোড করা যায়নি।',
                'products_failed' => 'প্রোডাক্ট লোড করা যায়নি।',
                'report_title' => 'বর্তমান স্টক রিপোর্ট',
                'generated' => 'তৈরির সময়',
                'no_match' => 'মিল পাওয়া যায়নি',
                'type_to_search' => 'লিখে খুঁজুন',
            ];
        @endphp

        {{-- One card, like the lot list: title + export buttons, filter row, table, count + pages --}}
        <div class="card card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap: 0.5rem;">
                    <h3 class="card-title mb-0 mr-2">{{ $text['list'] }}</h3>
                    {{-- Excel / Print buttons are placed here by the table script --}}
                    <div id="report-export" class="report-export"></div>
                </div>
            </div>

            <div class="card-body">
                {{-- Filters: page size, category, product, purchase type, search --}}
                <div class="form-row align-items-end mb-3">
                    <div class="col-6 col-md-4 col-xl-2 mb-2">
                        <label for="report_page_length" class="mb-1">{{ __('commontable.per_page') }}</label>
                        <select id="report_page_length" class="custom-select">
                            @foreach ([10, 25, 50, 100] as $size)
                                <option value="{{ $size }}">{{ $digits($size) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2 mb-2">
                        <label for="category_filter" class="mb-1">{{ __('stock.table_category') }}</label>
                        <select class="custom-select report-select" id="category_filter">
                            <option value="">{{ $text['all_categories'] }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2 mb-2">
                        <label for="product_filter" class="mb-1">{{ __('stock.table_product') }}</label>
                        <select class="custom-select report-select" id="product_filter" disabled>
                            <option value="">{{ $text['category_first'] }}</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2 mb-2">
                        <label for="purchase_type_filter" class="mb-1">{{ __('lot.purchase_type') }}</label>
                        <select class="custom-select report-select" id="purchase_type_filter">
                            <option value="">{{ $text['all_purchase_types'] }}</option>
                            @foreach ($purchaseTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Dates: the app's own calendar (same as the requisition list). The chosen date
                         is kept in a hidden input: #report-date-from-value / #report-date-to-value. --}}
                    <div class="col-6 col-md-4 col-xl-2 mb-2">
                        <label for="report-date-from" class="mb-1">{{ $text['date_from'] }}</label>
                        <div class="tw-root report-date">
                            <x-ui.date-field id="report-date-from" :placeholder="$text['pick_date']" compact clearable :pop-width="300" />
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2 mb-2">
                        <label for="report-date-to" class="mb-1">{{ $text['date_to'] }}</label>
                        <div class="tw-root report-date">
                            <x-ui.date-field id="report-date-to" :placeholder="$text['pick_date']" compact clearable :pop-width="300" />
                        </div>
                    </div>
                    <div class="col-12">
                        <label for="report_search" class="mb-1">{{ __('commontable.search') }}</label>
                        <input type="search" id="report_search" class="form-control" autocomplete="off"
                            placeholder="{{ $text['search'] }}" title="{{ $text['search'] }}">
                    </div>
                </div>

                <div class="table-responsive" id="stock-list-container">
                    {{-- Partial view loaded here --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var useBangla = @json(! $isEn);
            var T = @json($text);
            var table = null;

            function localDigits(value) {
                var text = String(value);
                return useBangla ? text.replace(/[0-9]/g, function(d) { return '০১২৩৪৫৬৭৮৯'[d]; }) : text;
            }

            // Category, product and purchase type: dropdowns with a search box, five options
            // visible at a time (the rest scroll). Styled in public/css/app-list.css.
            if ($.fn.select2) {
                $('.report-select').each(function() {
                    var select = $(this).select2({
                        width: '100%',
                        language: {
                            noResults: function() { return T.no_match; }
                        }
                    });
                    select.on('select2:open', function() {
                        // The open list is attached to the page body; mark it so it gets this page's look.
                        $('.select2-container--open .select2-dropdown').addClass('report-select-dropdown');
                        var field = document.querySelector('.select2-container--open .select2-search__field');
                        if (field) {
                            field.setAttribute('placeholder', T.type_to_search);
                            field.focus();
                        }
                    });
                });
            }

            // The searchable dropdown shows the select's current option again after its options change.
            function refreshSelect(select) {
                if ($.fn.select2) { select.trigger('change.select2'); }
            }

            // ---- Print preview (A4) ----
            function showDate(iso) {
                var parts = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso || '');
                if (!parts) { return ''; }
                return new Intl.DateTimeFormat(useBangla ? 'bn-BD' : 'en-GB', { day: 'numeric', month: 'long', year: 'numeric' })
                    .format(new Date(Number(parts[1]), Number(parts[2]) - 1, Number(parts[3])));
            }

            // Today's date in the Bangla calendar (Bangladesh, as revised in 2019): the year starts
            // on 14 April; the first six months have 31 days, Falgun 29 (30 in a leap year) and
            // the other five 30. E.g. 13 November 2026 is 28 Kartik 1433.
            function banglaDate(date) {
                var year = date.getFullYear();
                var start = new Date(year, 3, 14);
                if (date < start) {
                    year -= 1;
                    start = new Date(year, 3, 14);
                }
                var falgunYear = year + 1; // Falgun falls in February / March of the next Gregorian year
                var leap = (falgunYear % 4 === 0 && falgunYear % 100 !== 0) || falgunYear % 400 === 0;
                var lengths = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, leap ? 30 : 29, 30];
                var names = useBangla
                    ? ['বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র']
                    : ['Boishakh', 'Joishtho', 'Asharh', 'Srabon', 'Bhadro', 'Ashwin', 'Kartik', 'Ogrohayon', 'Poush', 'Magh', 'Falgun', 'Choitro'];
                var day = Math.round((Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()) - Date.UTC(start.getFullYear(), 3, 14)) / 86400000);
                var month = 0;
                while (month < 11 && day >= lengths[month]) {
                    day -= lengths[month];
                    month += 1;
                }
                return localDigits(day + 1) + ' ' + names[month] + ', ' + localDigits(year - 593) + (useBangla ? ' বঙ্গাব্দ' : ' BS');
            }
            window.reportBanglaDate = banglaDate;

            function escapeHtml(value) {
                return $('<div>').text(value == null ? '' : String(value)).html();
            }

            // Heading of the printed report: logo, office, report name, the filters in use and today's date
            function previewHeader() {
                var from = $('#report-date-from-value').val(), to = $('#report-date-to-value').val();
                var period = (!from && !to) ? T.period_all
                    : (showDate(from) || T.from_start) + ' ' + T.to + ' ' + (showDate(to) || T.till_today);
                var filters = [];
                $.each({ '#category_filter': @json(__('stock.table_category')), '#product_filter': @json(__('stock.table_product')), '#purchase_type_filter': @json(__('lot.purchase_type')) }, function(selector, label) {
                    if ($(selector).val()) {
                        filters.push(escapeHtml(label) + ': <strong>' + escapeHtml($(selector).find('option:selected').text()) + '</strong>');
                    }
                });
                var today = new Intl.DateTimeFormat(useBangla ? 'bn-BD' : 'en-GB', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date());

                return '<div class="a4-head">' +
                    '<img src="{{ asset('theme/assets/img/gov.png') }}" alt="">' +
                    '<h1>' + escapeHtml(T.ministry) + '</h1>' +
                    '<h2>' + escapeHtml(T.system) + '</h2>' +
                    '<h3>' + escapeHtml(T.report_title) + '</h3>' +
                    '<p>' + escapeHtml(T.period) + ': <strong>' + escapeHtml(period) + '</strong>' +
                    (filters.length ? ' &nbsp;|&nbsp; ' + filters.join(' &nbsp;|&nbsp; ') : '') + '</p>' +
                    '<p>' + escapeHtml(T.printed_on) + ': ' + escapeHtml(today) + ' &nbsp;|&nbsp; ' + escapeHtml(banglaDate(new Date())) + '</p>' +
                    '</div>';
            }

            // The preview window: real A4 sheets (210 x 297 mm) on a grey desk, one after the other,
            // with a small bar to print or close. The rows are dealt out over the sheets so that
            // each sheet is exactly one printed page; the column headings repeat on every sheet
            // and each sheet carries its page number.
            function setUpPreview(win) {
                var doc = win.document;
                $(doc.head).append('<style>' +
                    '@page { size: A4 portrait; margin: 0; }' +
                    'html, body { height: auto !important; }' +
                    'body { margin: 0 !important; padding: 0 !important; background: #e5e7eb !important; color: #0f172a; font-size: 11pt !important; }' +
                    '.a4-bar { position: sticky; top: 0; z-index: 10; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: #0f1f47; }' +
                    '.a4-bar button { min-height: 36px; padding: 0 16px; border: 1px solid transparent; border-radius: 8px; font: inherit; font-weight: 600; cursor: pointer; }' +
                    '.a4-bar .a4-print { background: #1d65db; color: #fff; }' +
                    '.a4-bar .a4-close { background: #fff; border-color: #cbd5e1; color: #334155; }' +
                    '.a4-bar .a4-size { margin-left: 8px; color: #cbd5e1; font-size: 10pt; }' +
                    '.a4-desk { overflow-x: auto; padding: 16px 0 24px; }' +
                    '.a4-sheet { position: relative; box-sizing: border-box; display: flex; flex-direction: column; width: 210mm; height: 297mm; margin: 0 auto 16px; padding: 12mm; overflow: hidden; background: #fff; box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.45); }' +
                    // Watermark: the government seal, faint, in the middle of every sheet and behind the
                    // text. It is an image (not a background), so it is printed without any extra setting.
                    // The seal is a vector file, so it stays sharp at this size on screen and on paper.
                    '.a4-mark { position: absolute; top: 50%; left: 50%; z-index: 0; width: 120mm; height: auto; transform: translate(-50%, -50%); opacity: 0.08; pointer-events: none; -webkit-print-color-adjust: exact; print-color-adjust: exact; }' +
                    '.a4-body, .a4-foot { position: relative; z-index: 1; }' +
                    '.a4-body { flex: 1 1 auto; min-height: 0; overflow: hidden; }' +
                    // A fixed height: the page number is written in after the rows are dealt out and
                    // must not take room away from them then.
                    '.a4-foot { box-sizing: border-box; flex: 0 0 8mm; height: 8mm; padding-top: 3mm; overflow: hidden; color: #475569; font-size: 9pt; line-height: 5mm; text-align: center; }' +
                    '.a4-top { margin-bottom: 3mm; color: #334155; font-size: 9.5pt; font-weight: 600; line-height: 5mm; text-align: right; white-space: pre; }' +
                    '.a4-sign { width: 70mm; margin: 22mm 0 0 auto; font-size: 10pt; line-height: 6.5mm; }' +
                    '.a4-sign-line { border-top: 1px solid #0f172a; }' +
                    '.a4-sign-title { padding-top: 1.5mm; font-weight: 700; text-align: center; }' +
                    '.a4-sign-field { margin-top: 1mm; border-bottom: 1px dotted #64748b; }' +
                    '.a4-head { margin-bottom: 14px; text-align: center; }' +
                    '.a4-head img { height: 64px; margin-bottom: 6px; }' +
                    '.a4-head h1 { margin: 0; font-size: 18pt; font-weight: 700; }' +
                    '.a4-head h2 { margin: 2px 0 0; font-size: 12pt; font-weight: 600; }' +
                    '.a4-head h3 { display: inline-block; margin: 8px 0 6px; padding: 2px 14px; border: 1px solid #0f172a; border-radius: 999px; font-size: 12pt; font-weight: 700; }' +
                    '.a4-head p { margin: 2px 0 0; font-size: 10pt; }' +
                    '.a4-sheet table { width: 100% !important; margin: 0 !important; border-collapse: collapse !important; table-layout: fixed; font-size: 10.5pt !important; }' +
                    '.a4-sheet table td { overflow-wrap: anywhere; }' +
                    // Cells are see-through so the watermark shows behind the table
                    '.a4-sheet table, .a4-sheet table tr { background: transparent !important; }' +
                    '.a4-sheet table th, .a4-sheet table td { padding: 5px 8px !important; border: 1px solid #94a3b8 !important; background: transparent !important; color: #0f172a !important; box-shadow: none !important; vertical-align: middle !important; }' +
                    '.a4-sheet table thead th { background: rgba(226, 232, 240, 0.55) !important; font-weight: 700; text-align: center; }' +
                    '.a4-sheet table tfoot th, .a4-sheet table tfoot td { background: rgba(241, 245, 249, 0.6) !important; font-weight: 700; }' +
                    '.a4-sheet table td:first-child, .a4-sheet table td:last-child { text-align: center; white-space: nowrap; }' +
                    '.a4-sheet table tfoot tr > :last-child { text-align: center !important; white-space: nowrap; }' +
                    '@media print {' +
                    // The framework's print rules give the body a minimum width of 992px, wider than
                    // an A4 page (794px); the browser then shrinks the whole page to make it fit.
                    // Here the body is exactly one A4 page wide, so each sheet fills its page.
                    ' html, html body { width: 210mm !important; min-width: 0 !important; max-width: none !important; margin: 0 !important; padding: 0 !important; background: #fff !important; }' +
                    ' .a4-bar { display: none !important; }' +
                    ' .a4-desk { overflow: visible; padding: 0; }' +
                    // A hair under 297mm, so that rounding can never push a sheet onto an extra blank page
                    ' .a4-sheet { height: 296.5mm; margin: 0; box-shadow: none; page-break-after: always; break-after: page; page-break-inside: avoid; break-inside: avoid; }' +
                    ' .a4-sheet:last-child { page-break-after: auto; break-after: auto; }' +
                    '}' +
                    '</style>');

                var body = $(doc.body);

                // On paper the category column is headed "Product category", and the total row
                // spans every column but the last one.
                var printed = body.find('table').first();
                printed.find('thead th').eq(1).text(T.product_category);
                var footCells = printed.find('tfoot tr').first().children();
                if (footCells.length) {
                    footCells.slice(1, -1).remove();
                    footCells.first().attr('colspan', Math.max(1, printed.find('thead th').length - 1)).css('text-align', 'right');
                }

                // Take the report apart ...
                var head = body.find('.a4-head').first().detach();
                var columns = printed.find('thead').first().detach();
                var rows = printed.find('tbody tr').detach().get();
                var total = printed.find('tfoot').first().detach();
                var tableClass = printed.attr('class') || '';

                body.empty();
                var bar = $('<div class="a4-bar"></div>');
                $('<button type="button" class="a4-print"></button>').text(T.print_now).on('click', function() { win.focus(); win.print(); }).appendTo(bar);
                $('<button type="button" class="a4-close"></button>').text(T.close).on('click', function() { win.close(); }).appendTo(bar);
                $('<span class="a4-size"></span>').text('A4 · ' + localDigits(210) + ' × ' + localDigits(297) + ' mm').appendTo(bar);
                var desk = $('<div class="a4-desk"></div>');
                body.append(bar).append(desk);
                doc.title = T.report_title;

                // Signature of the person who verifies the report (last sheet, bottom right)
                var signature = $('<div class="a4-sign"></div>')
                    .append($('<div class="a4-sign-line"></div>'))
                    .append($('<div class="a4-sign-title"></div>').text(T.verifier_signature))
                    .append($('<div class="a4-sign-field"></div>').text(T.sign_name + ':'))
                    .append($('<div class="a4-sign-field"></div>').text(T.sign_designation + ':'))
                    .append($('<div class="a4-sign-field"></div>').text(T.sign_date + ':'));

                // Shown at the top right of every sheet after the first
                var datesLine = new Intl.DateTimeFormat(useBangla ? 'bn-BD' : 'en-GB', { day: 'numeric', month: 'long', year: 'numeric' }).format(new Date())
                    + '  |  ' + banglaDate(new Date());

                // Anything that makes a table taller later on (a font that arrives late, a zoom
                // change) is noticed here; if a sheet's content then no longer fits, the sheets
                // are dealt out again.
                var watcher = null;
                if (win.ResizeObserver) {
                    var settle = null;
                    watcher = new win.ResizeObserver(function() {
                        win.clearTimeout(settle);
                        settle = win.setTimeout(function() {
                            var tooTall = desk.find('.a4-body').filter(function() {
                                return this.scrollHeight > this.clientHeight + 1;
                            }).length > 0;
                            if (tooTall) { paginate(); }
                        }, 80);
                    });
                }

                // ... and deal it out over A4 sheets: a row that does not fit starts the next sheet.
                function paginate() {
                    if (watcher) { watcher.disconnect(); }
                    $(rows).detach();
                    total.detach();
                    head.detach();
                    desk.empty();

                    var pages = [];
                    function newPage() {
                        var sheet = $('<div class="a4-sheet"><img class="a4-mark" alt="" src="{{ asset('Government_Seal_of_Bangladesh.svg') }}"><div class="a4-body"></div><div class="a4-foot"></div></div>').appendTo(desk);
                        // The same column widths on every sheet
                        var table = $('<table></table>').addClass(tableClass)
                            .append('<colgroup><col style="width:9%"><col style="width:27%"><col style="width:40%"><col style="width:24%"></colgroup>')
                            .append(columns.clone()).append('<tbody></tbody>');
                        var page = { content: sheet.find('.a4-body')[0], table: table, foot: sheet.find('.a4-foot') };
                        // From the second sheet on: today's date (Gregorian and Bangla) at the top right
                        if (pages.length > 0) {
                            $('<div class="a4-top"></div>').text(datesLine).appendTo(page.content);
                        }
                        $(page.content).append(table);
                        pages.push(page);

                        return page;
                    }
                    function overflows(page) {
                        return page.content.scrollHeight > page.content.clientHeight + 1;
                    }

                    var page = newPage();
                    $(page.content).prepend(head);

                    rows.forEach(function(row) {
                        var tbody = page.table.children('tbody');
                        tbody.append(row);
                        if (overflows(page) && tbody.children().length > 1) {
                            $(row).detach();
                            page = newPage();
                            page.table.children('tbody').append(row);
                        }
                    });

                    if (total.length) {
                        page.table.append(total);
                        if (overflows(page) && page.table.children('tbody').children().length > 0) {
                            total.detach();
                            page = newPage();
                            page.table.children('tbody').remove();
                            page.table.append(total);
                        }
                    }

                    // The verifier signs on the last sheet, under the table; when there is no room
                    // left there, the signature gets a sheet of its own.
                    signature.detach();
                    $(page.content).append(signature);
                    if (overflows(page)) {
                        signature.detach();
                        page = newPage();
                        page.table.remove();
                        $(page.content).append(signature);
                    }

                    pages.forEach(function(item, index) {
                        item.foot.text(T.page + ' ' + localDigits(index + 1) + ' / ' + localDigits(pages.length));
                        if (watcher) { watcher.observe(item.table[0]); }
                    });
                }

                paginate();
                // Row heights can change once the fonts, the logo and the style sheets of the new
                // window have loaded, so the sheets are dealt out again then - and once more just
                // before printing, so that what is printed always matches the sheets.
                $(win).on('load beforeprint', paginate);
                head.find('img').on('load', paginate);
                if (doc.fonts && doc.fonts.ready) {
                    doc.fonts.ready.then(paginate);
                }
                if (doc.fonts && doc.fonts.addEventListener) {
                    doc.fonts.addEventListener('loadingdone', paginate);
                }
                [300, 1000, 2500].forEach(function(delay) {
                    win.setTimeout(paginate, delay);
                });
            }

            // 1. Load the report. The filters above (category, product, purchase type) ask the
            //    server; page size and the search box work on the loaded rows.
            function loadStock() {
                $.ajax({
                    url: "{{ route('reports.stock.current') }}",
                    type: 'GET',
                    data: {
                        category_id: $('#category_filter').val(),
                        product_id: $('#product_filter').val(),
                        purchase_type_id: $('#purchase_type_filter').val(),
                        date_from: $('#report-date-from-value').val(),
                        date_to: $('#report-date-to-value').val()
                    },
                    beforeSend: function() {
                        $('#wait').show();
                    },
                    success: function(response) {
                        $('#wait').hide();
                        $('#report-export').empty();
                        $('#stock-list-container').html(response);

                        // Page size and search box live in the toolbar above the table,
                        // so DataTables' own controls are switched off (layout.top*).
                        table = $('#current-stock-table').DataTable({
                            paging: true,
                            pageLength: parseInt($('#report_page_length').val(), 10) || 10,
                            searching: true,
                            autoWidth: false,
                            // Rows stay in the order the report gives them until a column is clicked
                            order: [],
                            layout: {
                                topStart: null,
                                topEnd: null,
                                bottomStart: 'info',
                                bottomEnd: 'paging'
                            },
                            formatNumber: function(number) {
                                return localDigits(number);
                            },
                            language: {
                                info: @json(__('commontable.dt_info')),
                                infoEmpty: @json(__('commontable.dt_info_empty')),
                                infoFiltered: @json(__('commontable.dt_info_filtered')),
                                zeroRecords: @json(__('commontable.dt_zero_records')),
                                emptyTable: @json(__('commontable.no_record_found')),
                                paginate: {
                                    first: '«',
                                    previous: '‹',
                                    next: '›',
                                    last: '»'
                                },
                                aria: {
                                    paginate: {
                                        first: @json(__('commontable.dt_first')),
                                        previous: @json(__('commontable.dt_previous')),
                                        next: @json(__('commontable.dt_next')),
                                        last: @json(__('commontable.dt_last'))
                                    }
                                }
                            },
                            columnDefs: [{
                                    width: '6%',
                                    targets: 0,
                                    className: 'text-center',
                                    orderable: false,
                                    searchable: false
                                }, // SL
                                {
                                    width: '20%',
                                    targets: 1
                                }, // Category
                                {
                                    width: '24%',
                                    targets: 2
                                }, // Sub-category
                                {
                                    width: '30%',
                                    targets: 3
                                }, // Product
                                {
                                    width: '20%',
                                    targets: 4,
                                    className: 'text-center font-weight-bold'
                                } // Stocked quantity (with unit)
                            ]
                        });

                        // Excel / Print: shown in the card header, next to the title
                        new $.fn.dataTable.Buttons(table, {
                            buttons: [{
                                    // --- PRINT PREVIEW (A4) ---
                                    // Opens the report laid out on an A4 sheet in a new window, without
                                    // printing straight away; the window has its own "print" button.
                                    extend: 'print',
                                    text: '<i class="fas fa-eye"></i> ' + T.preview,
                                    autoPrint: false,
                                    footer: true,
                                    title: '',
                                    // Serial, category, product, stocked quantity (no sub-category on paper)
                                    exportOptions: {
                                        columns: [0, 1, 3, 4]
                                    },
                                    messageTop: function() {
                                        return previewHeader();
                                    },
                                    customize: function(win) {
                                        setUpPreview(win);
                                    }
                                },
                                {
                                    // --- EXCEL CONFIGURATION ---
                                    extend: 'excelHtml5',
                                    text: '<i class="fas fa-file-excel"></i> Excel',
                                    title: 'Ministry of Land - Inventory Management System',
                                    messageTop: 'Current Stock Report | Generated on: ' +
                                        new Date().toLocaleString(), // Subtitle
                                    exportOptions: {
                                        columns: ':visible' // Only export visible columns
                                    },
                                    customize: function(xlsx) {
                                        // Optional: Make the title row bold and larger
                                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                        $('row:first c', sheet).attr('s',
                                            '51'); // Apply a preset bold style
                                    }
                                },
                                {
                                    // --- PRINT CONFIGURATION (WITH LOGO) ---
                                    extend: 'print',
                                    text: '<i class="fas fa-print"></i> Print',
                                    title: '', // Remove default title to use our custom HTML below
                                    messageTop: `
                                        <div style="text-align: center; margin-bottom: 20px;">
                                            <img src="{{ asset('theme/assets/img/gov.png') }}" style="height: 80px; margin-bottom: 10px;">
                                            <h2 style="margin:0;">Ministry of Land</h2>
                                            <h4 style="margin:5px 0;">Inventory Management System</h4>
                                            <p style="margin:0;">Current Stock Report | Generated: ${new Date().toLocaleDateString()}</p>
                                        </div>
                                    `,
                                    customize: function(win) {
                                        $(win.document.body).css('font-size', '10pt');
                                        $(win.document.body).find('table')
                                            .addClass('compact')
                                            .css('font-size', 'inherit');
                                    }
                                },
                            ]
                        });
                        table.buttons().container().appendTo('#report-export');

                        // Serial numbers run 1..N over the rows as currently sorted and
                        // filtered, continuing across pages.
                        function renumber() {
                            var start = table.page.info().start;
                            table.rows({
                                search: 'applied',
                                order: 'applied',
                                page: 'current'
                            }).nodes().each(function(row, index) {
                                $(row).children('td').first().text(localDigits(start + index + 1));
                            });
                        }
                        table.on('draw', renumber);
                        renumber();

                        // Keep what is typed in the search box across a reload
                        if ($('#report_search').val()) {
                            table.search($('#report_search').val()).draw();
                        }
                    },
                    error: function(xhr) {
                        $('#wait').hide();
                        toastr.error(T.load_failed);
                    }
                });

            }

            // Initial load
            loadStock();

            $('#report_page_length').on('change', function() {
                if (table) { table.page.len(parseInt($(this).val(), 10) || 10).draw(); }
            });

            $('#report_search').on('input search', function() {
                if (table) { table.search($(this).val()).draw(); }
            });

            // Product, purchase type and the two dates: reload the report straight away
            $('#product_filter, #purchase_type_filter').on('change', function() {
                loadStock();
            });
            $('#report-date-from-value, #report-date-to-value').on('change', function() {
                // The end date cannot be before the start date, and the other way round
                // (the calendar reads its limits from data-min / data-max).
                $('#report-date-to-value').closest('[data-min]').attr('data-min', $('#report-date-from-value').val() || '');
                $('#report-date-from-value').closest('[data-max]').attr('data-max', $('#report-date-to-value').val() || '');
                loadStock();
            });

            // 2. Dependent Dropdown (Category -> Product), then reload the report
            $('#category_filter').on('change', function() {
                const categoryId = $(this).val();
                const productSelect = $('#product_filter');
                const urlTemplate = "{{ route('products.by_category', ['categoryId' => ':id']) }}";

                productSelect.prop('disabled', true).html($('<option value="">').text(T.loading));
                refreshSelect(productSelect);

                if (!categoryId) {
                    productSelect.html($('<option value="">').text(T.category_first));
                    refreshSelect(productSelect);
                    loadStock();
                    return;
                }

                const finalUrl = urlTemplate.replace(':id', categoryId);

                $.ajax({
                    url: finalUrl,
                    type: 'GET',
                    success: function(products) {
                        productSelect.empty().append($('<option value="">').text(T.all_products));
                        products.forEach(function(product) {
                            productSelect.append($('<option>').val(product.id).text(product.name));
                        });
                        productSelect.prop('disabled', false);
                        refreshSelect(productSelect);
                        loadStock();
                    },
                    error: function() {
                        toastr.error(T.products_failed);
                        productSelect.html($('<option value="">').text('—'));
                        refreshSelect(productSelect);
                        loadStock();
                    }
                });
            });
        });
    </script>
@endpush
