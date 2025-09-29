<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $fiscalYear->name }}</p>
</div>

<!-- Name Bn Field -->
<div class="col-sm-12">
    {!! Form::label('name_bn', 'Name Bn:') !!}
    <p>{{ $fiscalYear->name_bn }}</p>
</div>

<!-- Created By Field -->
<div class="col-sm-12">
    {!! Form::label('created_by', 'Created By:') !!}
    <p>{{ $fiscalYear->created_by }}</p>
</div>

<!-- Updated By Field -->
<div class="col-sm-12">
    {!! Form::label('updated_by', 'Updated By:') !!}
    <p>{{ $fiscalYear->updated_by }}</p>
</div>

<!-- Deleted By Field -->
<div class="col-sm-12">
    {!! Form::label('deleted_by', 'Deleted By:') !!}
    <p>{{ $fiscalYear->deleted_by }}</p>
</div>

