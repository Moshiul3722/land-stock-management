<!-- Role Id Field -->
<div class="col-sm-12">
    {!! Form::label('role_id', 'Role Id:') !!}
    <p>{{ $cnfPermission->role_id }}</p>
</div>

<!-- Menu Id Field -->
<div class="col-sm-12">
    {!! Form::label('menu_id', 'Menu Id:') !!}
    <p>{{ $cnfPermission->menu_id }}</p>
</div>

<!-- Created By Field -->
<div class="col-sm-12">
    {!! Form::label('created_by', 'Created By:') !!}
    <p>{{ $cnfPermission->created_by }}</p>
</div>

<!-- Updated By Field -->
<div class="col-sm-12">
    {!! Form::label('updated_by', 'Updated By:') !!}
    <p>{{ $cnfPermission->updated_by }}</p>
</div>

<!-- Deleted By Field -->
<div class="col-sm-12">
    {!! Form::label('deleted_by', 'Deleted By:') !!}
    <p>{{ $cnfPermission->deleted_by }}</p>
</div>

