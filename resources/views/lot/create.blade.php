@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('lot.add_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href={{ route('lots.index') }}>{{ __('breadcrumb.list') }}</a></li>
                        <li class="breadcrumb-item active"><a
                                href={{ route('lots.create') }}>{{ __('breadcrumb.create') }}</a></li>
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
                        <h3 class="card-title">{{ __('lot.add_title') }}</h3>
                    </div>
                    <form action="{{ route('lots.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            {{-- Lot Master Information --}}
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="lot_number">{{ __('lot.lot_number') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="lot_number" name="lot_number"
                                        value="{{ old('lot_number') }}" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="entry_date">{{ __('lot.entry_date') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="date" class="form-control" id="entry_date" name="entry_date"
                                        value="{{ old('entry_date', date('Y-m-d')) }}" required>
                                </div>
                                <div class="form-group col-md-12">
                                    <label for="remarks">{{ __('lot.remarks') }}</label>
                                    <textarea class="form-control" id="remarks" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                                </div>
                            </div>

                            <hr>
                            <h5 class="mb-3">Lot Details</h5>

                            {{-- Lot Details Table --}}
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('lot.table_product') }}</th>
                                        <th>{{ __('lot.table_quantity') }}</th>
                                        <th>{{ __('lot.table_unit_price') }}</th>
                                        <th><button type="button" id="add-row" class="btn btn-success btn-sm"><i
                                                    class="fa fa-plus"></i></button></th>
                                    </tr>
                                </thead>
                                <tbody id="lot-details-body">
                                    {{-- Rows will be added here by JS --}}
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                                {{ __('buttons.submit_for_approval') }}</button>
                            <a href="{{ route('lots.index') }}" class="btn btn-secondary"><i class="fas fa-ban"></i>
                                {{ __('buttons.cancel') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            let rowIdx = 0;
            $('#add-row').on('click', function() {
                $('#lot-details-body').append(`
            <tr id="R${++rowIdx}">
                <td>
                    <select class="form-control" name="details[${rowIdx}][product_id]" required>
                        <option value="">Select Product</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" name="details[${rowIdx}][quantity]" min="1" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="details[${rowIdx}][unit_price]" step="0.01" min="0">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button>
                </td>
            </tr>
        `);
            });

            // Remove row
            $('#lot-details-body').on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
            });

            // Add one row by default on page load
            $('#add-row').click();
        });
    </script>
@endpush
