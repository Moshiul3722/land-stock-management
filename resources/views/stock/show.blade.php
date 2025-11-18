@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('stock.details_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href={{ route('stock.index') }}>{{ __('breadcrumb.list') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.details') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Product: {{ $product->name }}</h3>
                    </div>
                    <div class="card-body">
                        <h4 class="mb-3">Stock Details by Lot</h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>{{ __('lot.table_lot_number') }}</th>
                                    <th>{{ __('lot.purchase_type') }}</th>
                                    <th>{{ __('stock.table_quantity') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($stockDetails as $stockEntry)
                                    <tr>
                                        <td>{{ $stockEntry->lot->lot_number ?? 'N/A' }}</td>
                                        <td>{{ $stockEntry->lot->purchaseType->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $stockEntry->quantity }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-end">Total Available Stock:</th>
                                    <th class="text-center">{{ $stockDetails->sum('quantity') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div class="card-footer">
                        <a href="{{ route('stock.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> {{ __('buttons.back_to_list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
