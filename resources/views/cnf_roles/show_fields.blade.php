<!-- Role Name En Field -->
<div class="col-sm-12">
    {!! Form::label('role_name_en', 'Role Name En:') !!}
    <p>{{ $cnfRole->role_name_en }}</p>
</div>

<!-- Role Name Bn Field -->
<div class="col-sm-12">
    {!! Form::label('role_name_bn', 'Role Name Bn:') !!}
    <p>{{ $cnfRole->role_name_bn }}</p>
</div>

<!-- Created By Field -->
<div class="col-sm-12">
    {!! Form::label('created_by', 'Created By:') !!}
    <p>{{ $cnfRole->created_by }}</p>
</div>

<!-- Updated By Field -->
<div class="col-sm-12">
    {!! Form::label('updated_by', 'Updated By:') !!}
    <p>{{ $cnfRole->updated_by }}</p>
</div>

<!-- Deleted By Field -->
<div class="col-sm-12">
    {!! Form::label('deleted_by', 'Deleted By:') !!}
    <p>{{ $cnfRole->deleted_by }}</p>
</div>

