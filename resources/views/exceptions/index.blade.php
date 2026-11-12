@extends('layouts.app')
@section('title', 'Exception Management')
@section('top-title', 'Exception Management')
@section('content')
<div class="pagehead">
    <div><h1>Exception Management</h1><div class="muted">Review and update shipment issues that need operational attention.</div></div>
</div>
<div class="panel" style="overflow-x:auto">
<table>
    <thead><tr><th>Shipment</th><th>Exception</th><th>Severity</th><th>Status</th><th>Detected</th><th>Update</th></tr></thead>
    <tbody>
    @forelse($exceptions as $exception)
        <tr>
            <td>
                <a href="{{ route('shipments.show', $exception->shipment) }}"><b>{{ $exception->shipment?->shipment_number ?? 'Shipment' }}</b></a>
                <div class="muted">{{ $exception->shipment?->customer?->name ?? '-' }}</div>
            </td>
            <td>{{ str_replace('_', ' ', $exception->type) }}<div class="muted">{{ $exception->description }}</div></td>
            <td><span class="badge {{ in_array($exception->severity, ['HIGH','CRITICAL']) ? 'red' : 'orange' }}">{{ $exception->severity }}</span></td>
            <td>{{ $exception->status }}</td>
            <td>{{ $exception->detected_at?->format('d M Y H:i') ?? '-' }}</td>
            <td>
                <form method="POST" action="{{ route('exceptions.update', $exception) }}" style="display:flex;gap:6px">
                    @csrf @method('PATCH')
                    <select name="status" style="border:1px solid #dce4ee;border-radius:7px;padding:7px">
                        @foreach(['OPEN','INVESTIGATING','RESOLVED'] as $status)
                            <option value="{{ $status }}" @selected($exception->status === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                    <button class="btn secondary">Save</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" style="text-align:center;padding:28px;color:#718096">No exceptions recorded.</td></tr>
    @endforelse
    </tbody>
</table>
<div style="margin-top:18px">{{ $exceptions->links() }}</div>
</div>
@endsection
