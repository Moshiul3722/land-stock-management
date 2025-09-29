<!-- Module Name En Field -->
<div class="col-sm-12">
    {!! Form::label('module_name_en', 'Module Name En:') !!}
    <p>{{ $cnfModule->module_name_en }}</p>
</div>

<!-- Module Name Bn Field -->
<div class="col-sm-12">
    {!! Form::label('module_name_bn', 'Module Name Bn:') !!}
    <p>{{ $cnfModule->module_name_bn }}</p>
</div>

<!-- Is Active Field -->
<div class="col-sm-12">
    {!! Form::label('is_active', 'Is Active:') !!}
    <p>{{ $cnfModule->is_active }}</p>
</div>

<!-- Created By Field -->
<div class="col-sm-12">
    {!! Form::label('created_by', 'Created By:') !!}
    <p>{{ $cnfModule->created_by }}</p>
</div>

<!-- Updated By Field -->
<div class="col-sm-12">
    {!! Form::label('updated_by', 'Updated By:') !!}
    <p>{{ $cnfModule->updated_by }}</p>
</div>

<!-- Deleted By Field -->
<div class="col-sm-12">
    {!! Form::label('deleted_by', 'Deleted By:') !!}
    <p>{{ $cnfModule->deleted_by }}</p>
</div>

