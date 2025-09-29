<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="cnf-modules-table">
            <thead>
            <tr>
                <th>Module Name En</th>
                <th>Module Name Bn</th>
                <th>Is Active</th>
                <th>Created By</th>
                <th>Updated By</th>
                <th>Deleted By</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cnfModules as $cnfModule)
                <tr>
                    <td>{{ $cnfModule->module_name_en }}</td>
                    <td>{{ $cnfModule->module_name_bn }}</td>
                    <td>{{ $cnfModule->is_active }}</td>
                    <td>{{ $cnfModule->created_by }}</td>
                    <td>{{ $cnfModule->updated_by }}</td>
                    <td>{{ $cnfModule->deleted_by }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['cnfModules.destroy', $cnfModule->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('cnfModules.show', [$cnfModule->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('cnfModules.edit', [$cnfModule->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $cnfModules])
        </div>
    </div>
</div>
