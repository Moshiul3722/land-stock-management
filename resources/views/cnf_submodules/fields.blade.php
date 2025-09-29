<!-- Submodule Name En Field -->
<div class="form-group col-sm-6">
    {!! Form::label('submodule_name_en', 'Submodule Name En:') !!}
    {!! Form::text('submodule_name_en', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Submodule Name Bn Field -->
<div class="form-group col-sm-6">
    {!! Form::label('submodule_name_bn', 'Submodule Name Bn:') !!}
    {!! Form::text('submodule_name_bn', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Module Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('module_id', 'Module Id:') !!}
    {!! Form::number('module_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Is Active Field -->
<div class="form-group col-sm-6">
    {!! Form::label('is_active', 'Is Active:') !!}
    {!! Form::number('is_active', null, ['class' => 'form-control']) !!}
</div>

<!-- Created By Field -->
<div class="form-group col-sm-6">
    {!! Form::label('created_by', 'Created By:') !!}
    {!! Form::number('created_by', null, ['class' => 'form-control']) !!}
</div>

<!-- Updated By Field -->
<div class="form-group col-sm-6">
    {!! Form::label('updated_by', 'Updated By:') !!}
    {!! Form::number('updated_by', null, ['class' => 'form-control']) !!}
</div>

<!-- Deleted By Field -->
<div class="form-group col-sm-6">
    {!! Form::label('deleted_by', 'Deleted By:') !!}
    {!! Form::number('deleted_by', null, ['class' => 'form-control']) !!}
</div>