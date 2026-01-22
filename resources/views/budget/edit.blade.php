@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Budget</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a></li>
                        <li class="breadcrumb-item"><a href={{ route('budgets.index') }}>Budget List</a></li>
                        <li class="breadcrumb-item active">{{ __('breadcrumb.edit') }}</li>
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
                        <h3 class="card-title">Edit Budget Allocation</h3>
                    </div>

                    {{-- Form ID for JS handling --}}
                    <form id="budget-form" action="{{ route('budgets.update', $budget->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                {{-- Read Only fields (Identity of the budget) --}}
                                <div class="col-md-6 form-group">
                                    <label>Category</label>
                                    <input type="text" class="form-control"
                                        value="{{ $budget->category->name ?? 'N/A' }}" disabled>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>Fiscal Year</label>
                                    {{-- Display the Name via Relationship --}}
                                    <input type="text" class="form-control"
                                        value="{{ $budget->fiscalYear->name ?? 'N/A' }}" disabled>
                                </div>

                                {{-- Editable fields --}}
                                <div class="col-md-6 form-group">
                                    <label for="amount">Budget Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" name="amount"
                                        value="{{ $budget->amount }}" required>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label for="remarks">Remarks</label>
                                    <textarea class="form-control" name="remarks" rows="2">{{ $budget->remarks }}</textarea>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" id="save-btn" class="btn btn-primary">
                                <i class="fas fa-save"></i> {{ __('buttons.update') }}
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
            // 1. Handle Server-Side Session Errors
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

            // 3. Handle Validation Errors (Multiple)
            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    toastr.error("{{ $error }}");
                @endforeach
            @endif

            // 4. Form Submission Confirmation
            $('#budget-form').on('submit', function(e) {
                e.preventDefault(); // Stop form from submitting immediately
                var form = this;

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to update this budget allocation.",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Update it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit the form programmatically
                    }
                });
            });
        });
    </script>
@endpush
