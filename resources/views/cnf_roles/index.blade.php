@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('rolemanagement.title') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href={{ route('home') }}>{{ __('breadcrumb.home') }}</a>
                        </li>
                        <li class="breadcrumb-item active">
                            {{ __('rolemanagement.title') }}
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <div class="content px-3">

        @include('flash::message')

        <div class="clearfix"></div>
        <div class="row">
        </div>
        <div class="card card-primary">
            @include('cnf_roles.table')
            <div class="card-footer clearfix">
                <div class="float-right">
                </div>
            </div>
        </div>
    </div>
@endsection
