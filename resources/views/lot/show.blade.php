@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('lot.details_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href={{ route('lots.index') }}>{{ __('breadcrumb.list') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">{{ __('lot.details_title') }}</h3>
                    </div>
                    <div class="card-body">
                        {{-- Lot Master Info --}}
                        <div class="row">
                            <div class="col-md-3">
                                <strong>{{ __('lot.lot_number') }}:</strong>
                                <p>{{ $lot->lot_number }}</p>
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('lot.entry_date') }}:</strong>
                                <p>{{ $lot->entry_date->format('F d, Y') }}</p>
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('lot.purchase_type') }}:</strong>
                                <p>{{ $lot->purchaseType->name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-3">
                                <strong>{{ __('lot.table_status') }}:</strong>
                                <p>
                                    @php
                                        $badgeClass = 'bg-secondary';
                                        if ($lot->status === 'pending') {
                                            $badgeClass = 'bg-warning text-dark';
                                        } elseif ($lot->status === 'approved') {
                                            $badgeClass = 'bg-success';
                                        } elseif ($lot->status === 'rejected') {
                                            $badgeClass = 'bg-danger';
                                        }
                                    @endphp
                                    <span class="badge {{ $badgeClass }}">{{ ucfirst($lot->status) }}</span>
                                </p>
                            </div>
                            <div class="col-md-12">
                                <strong>{{ __('lot.remarks') }}:</strong>
                                <p>{{ $lot->remarks ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <hr>

                        {{-- Lot Details Table --}}
                        <h5>Lot Items</h5>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th width="20%">{{ __('lot.table_category') }}</th>
                                    <th width="15%" style="display: none;">{{ __('lot.table_sub_category') }}</th>
                                    <th width="20%">{{ __('lot.table_product') }}</th>
                                    <th width="15%">{{ __('lot.brand') }}</th>
                                    <th width="15%">{{ __('lot.specification') }}</th>
                                    <th width="10%">{{ __('lot.table_quantity') }}</th>
                                    <th width="10%">{{ __('lot.price') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lot->lotDetails as $detail)
                                    <tr>
                                        <td>{{ $detail->product->category->name ?? 'N/A' }}</td>
                                        <td style="display: none;">
                                            {{-- Sub Category Logic --}}
                                            {{ $detail->product->subCategory->name ?? ($detail->product->sub_category->name ?? 'N/A') }}
                                        </td>
                                        <td>{{ $detail->product->name ?? 'N/A' }}</td>
                                        <td>{{ $detail->brand ?? 'N/A' }}</td>
                                        <td>{{ $detail->specification ?? 'N/A' }}</td>
                                        <td class="text-center font-weight-bold">{{ $detail->quantity }}</td>
                                        <td class="text-right">{{ number_format($detail->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('lots.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('buttons.back_to_list') }}
                        </a>

                        {{-- Edit Button (Only if Draft) --}}
                        @if ($lot->status === 'draft')
                            <a href="{{ route('lots.edit', $lot) }}" class="btn btn-warning float-right ml-2">
                                <i class="fas fa-edit"></i> {{ __('buttons.edit') }}
                            </a>
                        @endif

                        {{-- Approve/Reject Buttons (Only if Pending & Store Admin) --}}
                        {{-- Assuming Role ID 3 is Store Admin --}}
                        @if ($lot->status === 'pending' && (Auth::user()->role_id == 3 || Auth::user()->role_id == 2))
                            <button type="button" id="btn-reject" class="btn btn-danger float-right ml-2">
                                <i class="fas fa-times"></i> {{ __('lot.reject_lot') }}
                            </button>
                            <button type="button" id="btn-approve" class="btn btn-success float-right">
                                <i class="fas fa-check"></i> {{ __('lot.approve_stock_in') }}
                            </button>

                            {{-- Hidden Forms for Actions --}}
                            <form id="approve-form" action="{{ route('lots.approve', $lot->id) }}" method="POST"
                                style="display:none;">
                                @csrf
                                <input type="hidden" name="comments" id="approve-comments">
                            </form>
                            <form id="reject-form" action="{{ route('lots.reject', $lot->id) }}" method="POST"
                                style="display:none;">
                                @csrf
                                <input type="hidden" name="comments" id="reject-comments">
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {

            // Error Message (e.g., Budget Exceeded)
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Action Failed',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            @endif

            // Success Message
            @if (session('success'))
                toastr.success("{{ session('success') }}");
            @endif
            // Approve Button Logic
            $('#btn-approve').click(function() {
                Swal.fire({
                    title: 'Approve Lot?',
                    text: "This will add items to the live stock. This action cannot be undone.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve it!',
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#approve-comments').val(result.value);
                        $('#approve-form').submit();
                    }
                });
            });

            // Reject Button Logic
            $('#btn-reject').click(function() {
                Swal.fire({
                    title: 'Reject Lot?',
                    text: "Please provide a reason for rejection.",
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Reject it!',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'You need to write a reason!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#reject-comments').val(result.value);
                        $('#reject-form').submit();
                    }
                });
            });
        });
    </script>
@endpush
