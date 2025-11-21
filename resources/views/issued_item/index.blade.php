@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>My Issued Items</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href={{ route('home') }}>Home</a></li>
                        <li class="breadcrumb-item active">My Items</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="row">
            <div class="col-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-box-open mr-1"></i> Items Issued to Me
                        </h3>
                    </div>
                    <div class="card-body">
                        <table id="issued-items-table" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 5%" class="text-center">SL</th>
                                    <th style="width: 10%">Req. ID</th>
                                    <th style="width: 25%">Product Details</th>
                                    <th style="width: 15%">Purchase Type</th>
                                    <th style="width: 10%" class="text-center">Quantity</th>
                                    <th style="width: 15%">Req. Date</th>
                                    <th style="width: 15%">Issued Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge badge-secondary">#{{ $item->requisition_id }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $item->product->name ?? 'N/A' }}</strong><br>
                                            <small class="text-muted">{{ $item->product->category->name ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            {{ $item->lot->purchaseType->name ?? 'N/A' }}
                                            <br>
                                            <small class="text-muted">Lot: {{ $item->lot->lot_number ?? 'N/A' }}</small>
                                        </td>
                                        <td class="text-center font-weight-bold text-primary">
                                            {{ $item->quantity }}
                                        </td>
                                        <td>
                                            {{ optional($item->requisition)->requisition_date ? \Carbon\Carbon::parse($item->requisition->requisition_date)->format('d M, Y') : 'N/A' }}
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($item->issued_date)->format('d M, Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('#issued-items-table').DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": ["copy", "csv", "excel", "pdf", "print"],
                "order": [
                    [6, "desc"]
                ] // Order by Issued Date (Col index 6) descending
            }).buttons().container().appendTo('#issued-items-table_wrapper .col-md-6:eq(0)');
        });
    </script>
@endpush
