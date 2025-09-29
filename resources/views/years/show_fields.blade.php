<!-- Name Field -->
<div class="col-sm-12">
    {!! Form::label('name', 'Name:') !!}
    <p>{{ $year->name }}</p>
</div>

<!-- Name Bn Field -->
<div class="col-sm-12">
    {!! Form::label('name_bn', 'Name Bn:') !!}
    <p>{{ $year->name_bn }}</p>
</div>

<!-- Created By Field -->
<div class="col-sm-12">
    {!! Form::label('created_by', 'Created By:') !!}
    <p>{{ $year->created_by }}</p>
</div>

<!-- Updated By Field -->
<div class="col-sm-12">
    {!! Form::label('updated_by', 'Updated By:') !!}
    <p>{{ $year->updated_by }}</p>
</div>

<!-- Deleted By Field -->
<div class="col-sm-12">
    {!! Form::label('deleted_by', 'Deleted By:') !!}
    <p>{{ $year->deleted_by }}</p>
</div>

