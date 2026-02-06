@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('product.edit_product_title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href={{ route('products.index') }}>{{ __('breadcrumb.list') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            <a href='#'>{{ __('breadcrumb.edit') }}</a>
                        </li>
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
                        <h3 class="card-title">{{ __('product.add_product_title') }}</h3>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('products.update', $product) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                {{-- Product Name --}}
                                <div class="col-md-6 form-group mb-3">
                                    <label for="name">{{ __('product.product_name') }} <span
                                            style="color: red;">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="{{ old('name', $product->name) }}" required>
                                </div>

                                {{-- Product Unit (Autocomplete) --}}
                                <div class="col-md-6 form-group mb-3">
                                    <label for="product_unit">Product Unit <span style="color: red;">*</span></label>
                                    <input type="text" class="form-control autocomplete-unit" id="product_unit"
                                        name="product_unit" value="{{ old('product_unit', $product->product_unit) }}"
                                        placeholder="e.g. Pcs, Box, Kg" required>
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="category_id">{{ __('product.cateogory') }}</label> <span
                                    style="color: red;">*</span>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="description">{{ __('product.description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                            </div>
                            <div class="form-group mt-4">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                                    {{ __('buttons.update') }}</button>
                                <a href="{{ route('products.index') }}" class="btn btn-secondary"><i
                                        class="fas fa-ban"></i>
                                    {{ __('buttons.cancel') }}</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.js-example-basic-single').select2();

            // --- Autocomplete for Unit ---
            $('.autocomplete-unit').autocomplete({
                source: function(request, response) {
                    $.getJSON("{{ route('products.autocomplete_unit') }}", {
                        term: request.term
                    }, response);
                },
                minLength: 1
            });
        });
    </script>
@endpush
