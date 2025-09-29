<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="cnf-menus-table">
            <thead>
            <tr>
                <th>Submodule Id</th>
                <th>Menu Name En</th>
                <th>Menu Name Bn</th>
                <th>Controller</th>
                <th>Action</th>
                <th>Route</th>
                <th>Is Active</th>
                <th>Created By</th>
                <th>Updated By</th>
                <th>Deleted By</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cnfMenus as $cnfMenu)
                <tr>
                    <td>{{ $cnfMenu->submodule_id }}</td>
                    <td>{{ $cnfMenu->menu_name_en }}</td>
                    <td>{{ $cnfMenu->menu_name_bn }}</td>
                    <td>{{ $cnfMenu->controller }}</td>
                    <td>{{ $cnfMenu->action }}</td>
                    <td>{{ $cnfMenu->route }}</td>
                    <td>{{ $cnfMenu->is_active }}</td>
                    <td>{{ $cnfMenu->created_by }}</td>
                    <td>{{ $cnfMenu->updated_by }}</td>
                    <td>{{ $cnfMenu->deleted_by }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['cnfMenus.destroy', $cnfMenu->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('cnfMenus.show', [$cnfMenu->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('cnfMenus.edit', [$cnfMenu->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $cnfMenus])
        </div>
    </div>
</div>
