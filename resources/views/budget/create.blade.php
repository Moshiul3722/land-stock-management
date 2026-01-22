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

                    {{-- Form with ID for JavaScript handling --}}
                    <form id="budget-form" action="{{ route('budgets.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                {{-- Category Selection --}}
                                <div class="col-md-6 form-group">
                                    <label for="category_id">Category <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Fiscal Year --}}
                                <div class="col-md-6 form-group">
                                    <label for="fiscal_year">Fiscal Year <span class="text-danger">*</span></label>
                                    {{-- Uses defaultFiscalYear passed from controller, or old input if validation failed --}}
                                    <input type="text" class="form-control" name="fiscal_year"
                                        value="{{ old('fiscal_year', $defaultFiscalYear ?? date('Y') . '-' . (date('Y') + 1)) }}"
                                        placeholder="YYYY-YYYY" required>
                                    <small class="text-muted">Format: 2024-2025</small>
                                </div>

                                {{-- Amount --}}
                                <div class="col-md-6 form-group">
                                    <label for="amount">Budget Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="amount"
                                        value="{{ old('amount') }}" placeholder="0.00" required>
                                </div>

                                {{-- Remarks --}}
                                <div class="col-md-12 form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="2">{{ old('remarks') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer">
                            <button type="submit" id="save-btn" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('buttons.save') }}
                            </button>
                            <a href="{{ route('budgets.index') }}" class="btn btn-default float-right">
                                <i class="fas fa-ban"></i> {{ __('buttons.cancel') }}
                            </a>
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
            // Initialize Select2
            $('.select2').select2();

            // 1. CRITICAL: Handle Duplicate Entry Errors via SweetAlert
            // If the Service throws an exception (e.g., "Budget already exists"), the Controller redirects back with 'error'.
            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Operation Failed',
                    text: "{{ session('error') }}",
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            @endif

            // 2. Handle Success Messages
            @if (session('success'))
                toastr.success("{{ session('success') }}");
            @endif

            // 3. Handle Standard Validation Errors (e.g. Missing fields)
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}");
                @endforeach
            @endif

            // 4. Form Submission Confirmation
            $('#budget-form').on('submit', function(e) {
                e.preventDefault(); // Stop default submission
                var form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to save a new budget allocation.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Save it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Continue submission
                    }
                });
            });
        });
    </script>
@endpush
