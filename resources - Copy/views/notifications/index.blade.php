@extends('layouts.app')
@section('title', 'Notifications')
@section('top-title', 'Notifications')
@section('content')
<div class="pagehead">
    <div><h1>Notifications</h1><div class="muted">Updates and alerts for your CargoTrace account.</div></div>
</div>
<div class="panel">
    @forelse($notifications as $notification)
        <div style="padding:16px 0;border-bottom:1px solid #eef2f7;display:flex;justify-content:space-between;gap:18px;align-items:center">
            <div>
                <b>{{ $notification->title }}</b>
                <div class="muted" style="margin:5px 0">{{ $notification->message }}</div>
                <small class="muted">{{ $notification->created_at->format('d M Y H:i') }}</small>
                @if(!$notification->read_at)
                    <span class="badge orange">Unread</span>
                @else
                    <span class="badge green">Read</span>
                @endif
            </div>
            @if(!$notification->read_at)
                <form method="POST" action="{{ route('notifications.read', $notification) }}">
                    @csrf @method('PATCH')
                    <button class="btn secondary">Mark as read</button>
                </form>
            @endif
        </div>
    @empty
        <div class="muted">You don't have any notifications yet.</div>
    @endforelse
    <div style="margin-top:18px">{{ $notifications->links() }}</div>
</div>
@endsection
