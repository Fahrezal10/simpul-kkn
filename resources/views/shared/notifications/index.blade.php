@extends('layouts.app')

@section('title', 'Notifikasi')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <x-page-header title="Notifikasi" subtitle="Pemberitahuan terbaru untuk akun Anda." icon="bi-bell" />
        @if (Auth::user()->unreadNotifications->isNotEmpty())
            <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                @csrf
                <button type="submit" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-check2-all me-1"></i> Tandai Semua Dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="card app-card">
        <div class="card-body">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $isUnread = is_null($notification->read_at);
                    $notifUrl = $data['url'] ?? route('notifications.index');
                @endphp
                <a href="{{ $notifUrl }}"
                   class="notification-item d-flex align-items-start gap-3 py-2 text-decoration-none text-body {{ $isUnread ? 'notification-unread' : '' }}"
                   data-notif-id="{{ $notification->id }}">
                    <div class="notification-icon">
                        <i class="bi {{ ($data['status'] ?? '') === 'ditolak' ? 'bi-x-circle text-danger' : 'bi-info-circle text-primary' }}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="mb-1">{{ $data['message'] ?? 'Notifikasi' }}</div>
                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                    </div>
                </a>
            @empty
                <x-empty-state title="Tidak ada notifikasi" description="Notifikasi tentang status permohonan dan akun Anda akan muncul di sini." />
            @endforelse
        </div>
    </div>

    <div class="d-flex justify-content-end mt-3">
        {{ $notifications->links() }}
    </div>
@endsection