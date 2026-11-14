@extends('layouts.app')

@push('page_css')
    <link rel="stylesheet" href="{{ asset('css/tailwind.css') }}?v={{ @filemtime(public_path('css/tailwind.css')) }}">
@endpush

@section('content')
    <div class="container-fluid">
        <livewire:requisitions.manage-requisitions />
    </div>

    {{-- Details / decision dialog (the same one as in the requisition request list): opened from
         the list for requisitions the signed-in user can decide on. It sits outside the Livewire
         component, so list updates never rebuild it. --}}
    @include('requisition._decision_modal')
@endsection

@push('js')
    {{-- Result of an approval / rejection made from this page --}}
    <script>
        $(document).ready(function() {
            @if (session()->has('flash_notification'))
                @foreach (session('flash_notification', collect()) as $message)
                    toastr[@json(in_array($message['level'], ['danger', 'error']) ? 'error' : ($message['level'] === 'success' ? 'success' : ($message['level'] === 'warning' ? 'warning' : 'info')))](@json((string) $message['message']));
                @endforeach
                @php session()->forget('flash_notification') @endphp
            @endif
            @if (session('error'))
                toastr.error(@json(session('error')));
            @endif
            @if (session('success'))
                toastr.success(@json(session('success')));
            @endif
        });
    </script>
@endpush
