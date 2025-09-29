<div class="card-body p-0">
    <div class="table-responsive">
        <table class="table" id="cnf-religions-table">
            <thead>
            <tr>
                <th>Religion En</th>
                <th>Religion Bn</th>
                <th colspan="3">Action</th>
            </tr>
            </thead>
            <tbody>
            @foreach($cnfReligions as $cnfReligion)
                <tr>
                    <td>{{ $cnfReligion->religion_en }}</td>
                    <td>{{ $cnfReligion->religion_bn }}</td>
                    <td  style="width: 120px">
                        {!! Form::open(['route' => ['cnfReligions.destroy', $cnfReligion->id], 'method' => 'delete']) !!}
                        <div class='btn-group'>
                            <a href="{{ route('cnfReligions.show', [$cnfReligion->id]) }}"
                               class='btn btn-default btn-xs'>
                                <i class="far fa-eye"></i>
                            </a>
                            <a href="{{ route('cnfReligions.edit', [$cnfReligion->id]) }}"
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
            @include('adminlte-templates::common.paginate', ['records' => $cnfReligions])
        </div>
    </div>
</div>
