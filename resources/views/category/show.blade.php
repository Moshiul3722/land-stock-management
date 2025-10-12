@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Product Category Details</div>

                <div class="card-body">
                    <div class="mb-3">
                        <strong>Name:</strong>
                        <p>{{ $category->name }}</p>
                    </div>
                    <div class="mb-3">
                        <strong>Description:</strong>
                        <p>{{ $category->description ?? 'N/A' }}</p>
                    </div>

                    <div class="form-group mt-4">
                         <a href="{{ route('categories.index') }}" class="btn btn-primary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
