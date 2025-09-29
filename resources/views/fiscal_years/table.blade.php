<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="fiscal-years-table">
            <thead>
            <tr>
                <th>Name</th>
                <th>Name Bn</th>
                <th>Created By</th>
                <th>Updated By</th>
                <th>Deleted By</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($fiscalYears as $fiscalYear)
                <tr>
                    <td>{{ $fiscalYear->name }}</td>
                    <td>{{ $fiscalYear->name_bn }}</td>
                    <td>{{ $fiscalYear->created_by }}</td>
                    <td>{{ $fiscalYear->updated_by }}</td>
                    <td>{{ $fiscalYear->deleted_by }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['fiscalYears.destroy', $fiscalYear->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('fiscalYears.show', [$fiscalYear->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('fiscalYears.edit', [$fiscalYear->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-edit"></i>
                            </a>
                            {!! Form::button('<i class="far fa-trash-alt"></i>', ['type' => 'submit', 'class' => 'btn btn-danger btn-xs', 'onclick' => "return confirm('Are you sure?')"]) !!}
                        </div>
                        {!! Form::close() !!}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer clearfix">
        <div class="float-right">
            @include('adminlte-templates::common.paginate', ['records' => $fiscalYears])
        </div>
    </div>
</div>
