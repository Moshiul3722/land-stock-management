{{--
    Top of a vehicle management list page: title, breadcrumb, the list card with its
    "add" button and the search / page-size toolbar. Expects $t, $id and optionally $canAdd.
--}}
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ $t('title') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" wire:navigate>{{ __('breadcrumb.home') }}</a></li>
                    <li class="breadcrumb-item">{{ __('vehicle.module') }}</li>
                    <li class="breadcrumb-item active">{{ $t('title') }}</li>
                </ol>
            </div>
        </div>
    </div>
</section>
