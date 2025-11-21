@extends('layouts.app')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Notifications</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Notifications</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">All Notifications</h3>
                            <div class="card-tools">
                                <form action="{{ route('notifications.read_all') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-tool" title="Mark all as read">
                                        <i class="fas fa-check-double"></i> Mark all as read
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive mailbox-messages">
                                <table class="table table-hover table-striped">
                                    <tbody>
                                        @forelse($notifications as $notification)
                                            <tr class="{{ $notification->is_read ? '' : 'font-weight-bold bg-light' }}">
                                                <td class="mailbox-star">
                                                    @if(!$notification->is_read)
                                                        <a href="{{ route('notifications.read', $notification->id) }}"><i class="fas fa-circle text-primary text-xs"></i></a>
                                                    @else
                                                        <i class="far fa-circle text-muted text-xs"></i>
                                                    @endif
                                                </td>
                                                <td class="mailbox-name">
                                                    <a href="{{ route('notifications.read', $notification->id) }}" class="{{ $notification->is_read ? 'text-dark' : 'text-primary' }}">
                                                        {{ $notification->userFrom->name ?? 'System' }}
                                                    </a>
                                                </td>
                                                <td class="mailbox-subject">
                                                    {{ $notification->notification_text }}
                                                </td>
                                                <td class="mailbox-date">{{ $notification->created_at->diffForHumans() }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">
                                                    <i class="far fa-bell-slash fa-2x mb-2"></i><br>
                                                    No notifications found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer p-0">
                            <div class="mailbox-controls">
                                <div class="float-right">
                                    {{ $notifications->links('pagination::bootstrap-4') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection