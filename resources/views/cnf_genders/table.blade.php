<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="cnf-genders-table">
            <thead>
            <tr>
                <th>Gender En</th>
                <th>Gender Bn</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cnfGenders as $cnfGender)
                <tr>
                    <td>{{ $cnfGender->gender_en }}</td>
                    <td>{{ $cnfGender->gender_bn }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['cnfGenders.destroy', $cnfGender->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('cnfGenders.show', [$cnfGender->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('cnfGenders.edit', [$cnfGender->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $cnfGenders])
        </div>
    </div>
</div>
