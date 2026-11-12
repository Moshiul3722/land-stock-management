@extends('layouts.app')

@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
@endpush

@section('content')
    <div class="container-fluid">
        <livewire:requisitions.manage-requisitions />
    </div>
@endsection
