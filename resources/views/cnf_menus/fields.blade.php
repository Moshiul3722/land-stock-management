<!-- Submodule Id Field -->
<div class="form-group col-sm-6">
    {!! Form::label('submodule_id', 'Submodule Id:') !!}
    {!! Form::number('submodule_id', null, ['class' => 'form-control']) !!}
</div>

<!-- Menu Name En Field -->
<div class="form-group col-sm-6">
    {!! Form::label('menu_name_en', 'Menu Name En:') !!}
    {!! Form::text('menu_name_en', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Menu Name Bn Field -->
<div class="form-group col-sm-6">
    {!! Form::label('menu_name_bn', 'Menu Name Bn:') !!}
    {!! Form::text('menu_name_bn', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Controller Field -->
<div class="form-group col-sm-6">
    {!! Form::label('controller', 'Controller:') !!}
    {!! Form::text('controller', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Action Field -->
<div class="form-group col-sm-6">
    {!! Form::label('action', 'Action:') !!}
    {!! Form::text('action', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
</div>

<!-- Route Field -->
<div class="form-group col-sm-6">
    {!! Form::label('route', 'Route:') !!}
    {!! Form::text('route', null, ['class' => 'form-control', 'maxlength' => 255, 'maxlength' => 255]) !!}
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