@extends('layouts.app')

@section('title', 'Archived Shipments')
@section('top-title', 'Archived Shipments')

@section('content')
<div style="max-width:1200px;margin:0 auto;color:#172b49">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:20px;flex-wrap:wrap">
        <div>
            <h1 style="font-size:25px;margin:0 0 5px">Archived Shipments</h1>
            <p style="color:#8997aa;margin:0;font-size:13px">Shipment yang diarsipkan tetap tersimpan dan dapat dipulihkan oleh Admin.</p>
        </div>
        <a href="{{ route('shipments.index') }}" style="padding:10px 14px;border:1px solid #dce4ee;border-radius:8px;color:#334b68;text-decoration:none;background:#fff">← Kembali ke Shipments</a>
    </div>

    @if(session('success'))
        <div style="padding:12px 15px;margin-bottom:15px;border-radius:8px;background:#eaf8ef;color:#246b3c">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('shipments.archived') }}" style="display:flex;gap:10px;margin-bottom:15px;flex-wrap:wrap">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nomor shipment atau customer..." style="flex:1;min-width:220px;padding:11px 13px;border:1px solid #dce4ee;border-radius:8px">
        <button type="submit" style="padding:10px 16px;border:0;border-radius:8px;background:#203b5d;color:#fff;cursor:pointer">Cari</button>
        @if(request('q'))<a href="{{ route('shipments.archived') }}" style="padding:10px 14px;color:#53677f;text-decoration:none">Reset</a>@endif
    </form>

    <div style="background:#fff;border:1px solid #e8edf4;border-radius:12px;overflow:auto">
        <table style="width:100%;border-collapse:collapse;min-width:850px;font-size:13px">
            <thead style="background:#f7f9fc;color:#718198;text-align:left">
                <tr><th style="padding:13px">Shipment</th><th style="padding:13px">Customer</th><th style="padding:13px">Route</th><th style="padding:13px">Status terakhir</th><th style="padding:13px">Archived at</th><th style="padding:13px">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($shipments as $shipment)
                <tr style="border-top:1px solid #edf1f6">
                    <td style="padding:13px;font-weight:700">{{ $shipment->shipment_number }}</td>
                    <td style="padding:13px">{{ $shipment->customer?->name ?? '—' }}</td>
                    <td style="padding:13px">{{ $shipment->origin?->city ?? $shipment->origin?->name ?? '—' }} → {{ $shipment->destination?->city ?? $shipment->destination?->name ?? '—' }}</td>
                    <td style="padding:13px">{{ str_replace('_',' ', $shipment->current_status) }}</td>
                    <td style="padding:13px">{{ $shipment->deleted_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td style="padding:13px;white-space:nowrap">
                        @if(auth()->user()->role === 'admin')
                            <form method="POST" action="{{ route('shipments.restore', $shipment->id) }}" onsubmit="return confirm('Pulihkan shipment ini ke daftar aktif?')" style="display:inline">
                                @csrf @method('PATCH')
                                <button type="submit" style="padding:7px 11px;border:0;border-radius:7px;background:#e9f6ee;color:#267044;font-weight:700;cursor:pointer">Restore</button>
                            </form>
                        @else
                            <span style="color:#8a98aa">Read only — Admin dapat memulihkan</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="padding:30px;text-align:center;color:#8a98aa">Belum ada shipment yang diarsipkan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:16px">{{ $shipments->links() }}</div>
</div>
@endsection
