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
                            <a href='#'>{{ __('breadcrumb.details') }}</a>
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
                        <h3 class="card-title">{{ __('product.description') }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>{{ __('product.product_name') }}:</strong>
                            <p>{{ $product->name }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('product.cateogory') }}:</strong>
                            <p>{{ $product->category->name ?? 'N/A' }}</p>
                        </div>
                        <div class="mb-3">
                            <strong>{{ __('product.description') }}:</strong>
                            <p>{{ $product->description ?? 'N/A' }}</p>
                        </div>
                        <a href="{{ route('products.index') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> {{ __('buttons.back_to_list') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
