@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>{{ __('requisition.my_title') }}</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('requisition.my_title') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        {{-- Filter Card --}}
        <div class="card card-primary">
            <div class="card-header"><h3 class="card-title">{{ __('menupermission.filter') }}</h3></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label>{{ __('lot.table_status') }}</label>
                        <select class="form-control" id="status_filter">
                            <option value="">All</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>{{ __('requisition.start_date') }}</label>
                        <input type="date" class="form-control" id="start_date">
                    </div>
                    <div class="col-md-3">
                        <label>{{ __('requisition.end_date') }}</label>
                        <input type="date" class="form-control" id="end_date">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button id="filter-btn" class="btn btn-primary w-100"><i class="fas fa-search"></i> {{ __('buttons.search') }}</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- List Card --}}
        <div class="card card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">{{ __('requisition.list_title') }}</h3>
                <a href="{{ route('requisitions.create') }}" class="btn btn-sm btn-success ms-auto"><i class="fas fa-plus"></i></a>
            </div>
            <div class="card-body" id="requisition-list-container">
                {{-- Partial view loads here --}}
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        function loadRequisitions() {
            $.ajax({
                url: "{{ route('requisitions.index') }}",
                type: "GET",
                data: {
                    status: $('#status_filter').val(),
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                },
                beforeSend: function() { $('#wait').show(); },
                success: function(res) {
                    $('#wait').hide();
                    $('#requisition-list-container').html(res);
                    $('#my-req-table').DataTable();
                },
                error: function() { $('#wait').hide(); toastr.error('Error loading data'); }
            });
        }

        loadRequisitions(); // Initial load

        $('#filter-btn').click(function() {
            loadRequisitions();
        });
    });
</script>
@endpush
