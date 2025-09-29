<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="years-table">
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
            @foreach($years as $year)
                <tr>
                    <td>{{ $year->name }}</td>
                    <td>{{ $year->name_bn }}</td>
                    <td>{{ $year->created_by }}</td>
                    <td>{{ $year->updated_by }}</td>
                    <td>{{ $year->deleted_by }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['years.destroy', $year->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('years.show', [$year->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('years.edit', [$year->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $years])
        </div>
    </div>
</div>
