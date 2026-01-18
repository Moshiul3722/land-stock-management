@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Define Budget</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href={{ route('budgets.index') }}>Budget List</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.create') }}</li>
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
                        <h3 class="card-title">New Budget Allocation</h3>
                    </div>
                    <form action="{{ route('budgets.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="fiscal_year">Fiscal Year <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="fiscal_year" value="{{ $defaultFiscalYear }}" placeholder="YYYY-YYYY" required>
                                    <small class="text-muted">Format: 2024-2025</small>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="amount">Budget Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="amount" placeholder="0.00" required>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Budget</button>
                            <a href="{{ route('budgets.index') }}" class="btn btn-default float-right">Cancel</a>
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
        $('.select2').select2();
    });
</script>
@endpush