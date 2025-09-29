<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="cnf-submodules-table">
            <thead>
            <tr>
                <th>Submodule Name En</th>
                <th>Submodule Name Bn</th>
                <th>Module Id</th>
                <th>Is Active</th>
                <th>Created By</th>
                <th>Updated By</th>
                <th>Deleted By</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cnfSubmodules as $cnfSubmodule)
                <tr>
                    <td>{{ $cnfSubmodule->submodule_name_en }}</td>
                    <td>{{ $cnfSubmodule->submodule_name_bn }}</td>
                    <td>{{ $cnfSubmodule->module_id }}</td>
                    <td>{{ $cnfSubmodule->is_active }}</td>
                    <td>{{ $cnfSubmodule->created_by }}</td>
                    <td>{{ $cnfSubmodule->updated_by }}</td>
                    <td>{{ $cnfSubmodule->deleted_by }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['cnfSubmodules.destroy', $cnfSubmodule->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('cnfSubmodules.show', [$cnfSubmodule->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('cnfSubmodules.edit', [$cnfSubmodule->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $cnfSubmodules])
        </div>
    </div>
</div>
