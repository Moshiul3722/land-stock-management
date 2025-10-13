@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Product Details</div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong>
                        <p>{{ $product->name }}</p>
                    </div>
                     <div class="mb-3">
                        <strong>Category:</strong>
                        <p>{{ $product->category->name ?? 'N/A' }}</p>
                    </div>
                     <div class="mb-3">
                        <strong>Purchase Type:</strong>
                        <p>{{ $product->purchaseType->name ?? 'N/A' }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p>{{ $product->description ?? 'N/A' }}</p>
                    </div>
                    <a href="{{ route('products.index') }}" class="btn btn-primary">Back to List</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
