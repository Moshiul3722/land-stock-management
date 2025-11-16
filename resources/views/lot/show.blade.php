@extends('layouts.app')

@section('content')
    <section class="content-header">
         <div class="container-fluid">
            {{-- Breadcrumbs --}}
        </div>
    </section>
    <div class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="card card-primary">
                    <div class="card-header"><h3 class="card-title">{{ __('lot.details_title') }}</h3></div>
                    <div class="card-body">
                        {{-- Lot Master Info --}}
                        <div class="row">
                            <div class="col-md-4"><strong>{{ __('lot.lot_number') }}:</strong><p>{{ $lot->lot_number }}</p></div>
                            <div class="col-md-4"><strong>{{ __('lot.entry_date') }}:</strong><p>{{ $lot->entry_date->format('F d, Y') }}</p></div>
                            <div class="col-md-4"><strong>{{ __('lot.table_status') }}:</strong><p><span class="badge bg-info">{{ ucfirst($lot->status) }}</span></p></div>
                            <div class="col-md-12"><strong>{{ __('lot.remarks') }}:</strong><p>{{ $lot->remarks ?? 'N/A' }}</p></div>
                        </div>
                        <hr>
                        {{-- Lot Details Table --}}
                        <h5>Lot Items</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('lot.table_product') }}</th>
                                    <th>{{ __('lot.table_quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($lot->lotDetails as $detail)
                                <tr>
                                    <td>{{ $detail->product->name ?? 'N/A' }}</td>
                                    <td>{{ $detail->quantity }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('lots.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> {{ __('buttons.back_to_list') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
