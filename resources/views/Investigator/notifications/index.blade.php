@extends('layouts.investigator')

@section('content')
<style>
    .breadcrumb-bar {
        margin-top: -20px;
        margin-left: 2rem;
        margin-bottom: 1.5rem;
    }

    .system-card {
        background-color: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.05);
        padding: 2.5rem;
        margin-bottom: 2rem;
    }

    .list-group-item {
        border-radius: 0.5rem;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.08);
        margin-bottom: 0.75rem;
        position: relative;
    }

    .list-group-item-info {
        background-color: #d1e7ff;
        color: #084298;
    }

    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border-radius: 0.4rem;
    }

    .float-end {
        position: absolute;
        top: 0.75rem;
        right: 1rem;
    }

    .notification-title {
        font-weight: 600;
    }

    .notification-time {
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>

<div class="container py-5">
    <!-- Breadcrumb -->
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('investigator/dash') }}"><i class="fas fa-home"></i></a></li>
                <li class="breadcrumb-item active" aria-current="page">Notifications</li>
            </ol>
        </nav>
    </div>

    <div class="system-card">
        <h3 class="mb-4 text-primary fw-bold text-center">Notifications</h3>

        @if($notifications->count() > 0)
            <ul class="list-group">
                @foreach($notifications as $notification)
                    <li class="list-group-item @if(!$notification->read_at) list-group-item-info @endif">
                        <div class="notification-title">{{ $notification->data['title'] ?? 'Notification' }}</div>
                        <div>{{ $notification->data['message'] ?? '' }}</div>
                        <small class="notification-time">Received: {{ $notification->created_at->diffForHumans() }}</small>

                        @if(!$notification->read_at)
                            <form action="{{ route('notifications.markAsRead', $notification->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button class="btn btn-sm btn-primary float-end">Mark as read</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="d-flex justify-content-center mt-4">
                {{ $notifications->links() }}
            </div>
        @else
            <p class="text-muted text-center">No notifications found.</p>
        @endif
    </div>
</div>
@endsection
