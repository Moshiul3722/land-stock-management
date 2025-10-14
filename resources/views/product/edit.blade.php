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
                            <div class="form-group mb-3">
                                <label for="name">{{ __('product.product_name') }}</label> <span style="color: red;">*</span>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $product->name) }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label for="category_id">{{ __('product.cateogory') }}</label> <span style="color: red;">*</span>
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
                                <label for="purchase_type_id">{{ __('product.purchase_type') }}</label> <span style="color: red;">*</span>
                                <select class="form-control" id="purchase_type_id" name="purchase_type_id" required>
                                    <option value="">Select Purchase Type</option>
                                    @foreach ($purchaseTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('purchase_type_id', $product->purchase_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="description">{{ __('product.description') }}</label>
                                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> {{ __('buttons.update') }}</button>
                            <a href="{{ route('products.index') }}" class="btn btn-secondary"><i class="fas fa-ban"></i>
                                {{ __('buttons.cancel') }}</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
