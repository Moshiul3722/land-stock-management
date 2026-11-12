{{-- Page shell for a Livewire CRUD screen. $component is the Livewire component name. --}}
@extends('layouts.app')

@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/app-crud.css') }}?v={{ @filemtime(public_path('css/app-crud.css')) }}">
@endpush

@section('content')
    @livewire($component)
@endsection
