
@extends('layouts.app')

@section('title', 'Shipment Details')
@section('top-title', 'Shipment Details')

@php
    $status = $shipment->current_status ?? 'CREATED';

    $statusLabels = [
        'CREATED' => 'Created',
        'BOOKED' => 'Booked',
        'PICKED_UP' => 'Picked Up',
        'IN_TRANSIT' => 'In Transit',
        'DELIVERED' => 'Delivered',
    ];

    $statusColors = [
        'CREATED' => '#64748b',
        'BOOKED' => '#2563eb',
        'PICKED_UP' => '#7c3aed',
        'IN_TRANSIT' => '#d97706',
        'DELIVERED' => '#16a34a',
    ];

    $isLate = $shipment->expected_arrival_at
        && $shipment->expected_arrival_at->isPast()
        && $status !== 'DELIVERED';

    $canManage = in_array(auth()->user()->role, ['admin', 'operator'], true);

    $formatDate = function ($date) {
        return $date ? $date->format('d M Y, H:i') : 'Not set';
    };
@endphp

@push('head')
<style>
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 22px;
    }

    .detail-header h1 {
        font-size: 25px;
        color: #172b4d;
        margin: 0 0 6px;
    }

    .detail-header p {
        font-size: 12px;
        color: #8492a7;
        margin: 0;
    }

    .detail-actions {
        display: flex;
        gap: 9px;
        flex-wrap: wrap;
    }

    .detail-card {
        background: #fff;
        border: 1px solid #e6edf5;
        border-radius: 13px;
        padding: 20px;
        margin-bottom: 17px;
        box-shadow: 0 3px 14px #19385b05;
    }

    .detail-card h2 {
        font-size: 15px;
        color: #203653;
        margin: 0 0 17px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .detail-item small {
        display: block;
        font-size: 10px;
        font-weight: 700;
        color: #91a0b3;
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .detail-item strong {
        font-size: 12px;
        color: #263d5a;
        overflow-wrap: anywhere;
    }

    .status-badge {
        display: inline-block;
        padding: 7px 13px;
        border-radius: 20px;
        color: #fff;
        font-size: 10px;
        font-weight: 800;
    }

    .route-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .route-box {
        background: #f7f9fc;
        padding: 17px;
        border-radius: 10px;
    }

    .route-box small {
        display: block;
        font-size: 10px;
        color: #8492a7;
        margin-bottom: 7px;
    }

    .route-box strong {
        font-size: 14px;
        color: #203653;
    }

    .detail-table-wrap {
        overflow-x: auto;
    }

    .detail-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 550px;
        font-size: 11px;
    }

    .detail-table th,
    .detail-table td {
        text-align: left;
        padding: 12px;
        border-bottom: 1px solid #edf1f6;
    }

    .detail-table th {
        color: #8492a7;
        font-size: 10px;
        text-transform: uppercase;
    }

    .detail-table td {
        color: #51627a;
    }

    .leg-actions {
        display: flex;
        align-items: center;
        gap: 7px;
        flex-wrap: wrap;
        min-width: 115px;
    }

    .leg-action-btn {
        display: inline-block;
        border: 1px solid #dce4ee;
        border-radius: 6px;
        padding: 6px 10px;
        background: #fff;
        color: #2864e8;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }

    .leg-action-btn.delete {
        border-color: #f1c8cc;
        color: #c62828;
    }

    .leg-edit-panel {
        margin-top: 8px;
        min-width: 260px;
        padding: 12px;
        background: #f8fafc;
        border: 1px solid #e5edf5;
        border-radius: 8px;
    }

    .leg-edit-panel summary {
        color: #2864e8;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        margin-bottom: 8px;
    }

    .leg-edit-panel .detail-form {
        gap: 9px;
    }

    .leg-edit-panel .detail-form label {
        font-size: 10px;
    }

    .leg-edit-panel .detail-form input,
    .leg-edit-panel .detail-form select,
    .leg-edit-panel .detail-form textarea {
        padding: 8px;
        font-size: 11px;
    }

    .detail-form {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .detail-form .full {
        grid-column: 1 / -1;
    }

    .detail-form label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #52647d;
        margin-bottom: 6px;
    }

    .detail-form input,
    .detail-form select,
    .detail-form textarea {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #dce4ee;
        border-radius: 8px;
        font-size: 12px;
        color: #263d5a;
        background: #fff;
    }

    .detail-form textarea {
        min-height: 85px;
        resize: vertical;
    }

    .detail-form input:focus,
    .detail-form select:focus,
    .detail-form textarea:focus {
        outline: none;
        border-color: #2864e8;
    }

    .tracking-item {
        border-left: 3px solid #2864e8;
        padding: 3px 0 18px 15px;
        margin-left: 5px;
        position: relative;
    }

    .tracking-item:last-child {
        border-left-color: transparent;
        padding-bottom: 0;
    }

    .tracking-item::before {
        content: '';
        position: absolute;
        left: -7px;
        top: 2px;
        width: 11px;
        height: 11px;
        border-radius: 50%;
        background: #2864e8;
    }

    .tracking-item strong {
        color: #263d5a;
        font-size: 12px;
    }

    .tracking-item p {
        font-size: 11px;
        color: #78889d;
        margin: 5px 0;
    }

    .tracking-item small {
        font-size: 10px;
        color: #9aa7b8;
    }

    .map-container {
        width: 100%;
        height: 320px;
        border-radius: 10px;
        background: #eef3f8;
        z-index: 1;
    }

    .alert-box {
        padding: 13px 15px;
        border-radius: 9px;
        margin-bottom: 15px;
        font-size: 12px;
    }

    .alert-warning {
        color: #a45c00;
        background: #fff6e6;
    }

    .alert-success {
        color: #16805b;
        background: #e8f7ef;
    }

    .alert-danger {
        color: #b42318;
        background: #fff0f0;
    }

    .empty-state {
        padding: 20px;
        text-align: center;
        color: #91a0b3;
        font-size: 11px;
    }

    @media(max-width: 800px) {
        .detail-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 600px) {
        .detail-grid,
        .route-grid,
        .detail-form {
            grid-template-columns: 1fr;
        }

        .detail-form .full {
            grid-column: auto;
        }

        .detail-card {
            padding: 15px;
        }

        .detail-header h1 {
            font-size: 21px;
        }
    }

    /* Polished transport-leg actions: compact buttons with an anchored edit popover */
    .detail-table { min-width: 900px; }
    .detail-table th:last-child, .detail-table td:last-child { width: 150px; min-width: 150px; }
    .detail-table td:last-child { vertical-align: middle; }
    .leg-actions { display: flex; align-items: center; justify-content: flex-start; gap: 8px; flex-wrap: nowrap; min-width: 0; position: relative; }
    .leg-edit-panel { display: inline-block; margin: 0; min-width: 0; padding: 0; background: transparent; border: 0; border-radius: 0; position: relative; }
    .leg-edit-panel summary { list-style: none; display: inline-flex; align-items: center; justify-content: center; margin: 0; padding: 7px 13px; border: 1px solid #dbe5f2; border-radius: 7px; background: #fff; color: #2864e8; font-size: 12px; font-weight: 700; cursor: pointer; white-space: nowrap; }
    .leg-edit-panel summary::-webkit-details-marker { display: none; }
    .leg-edit-panel[open] summary { background: #eef4ff; border-color: #b8ccff; }
    .leg-edit-panel .detail-form { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; position: absolute; top: calc(100% + 8px); right: 0; width: min(480px, 78vw); padding: 16px; background: #fff; border: 1px solid #dbe5f2; border-radius: 12px; box-shadow: 0 12px 32px rgba(24, 48, 80, .18); z-index: 50; }
    .leg-edit-panel .detail-form .full { grid-column: 1 / -1; }
    .leg-edit-panel .detail-form label { margin: 0 0 5px; color: #52647d; font-size: 11px; }
    .leg-edit-panel .detail-form input, .leg-edit-panel .detail-form select, .leg-edit-panel .detail-form textarea { width: 100%; min-width: 0; padding: 9px 10px; border: 1px solid #dce4ee; border-radius: 7px; background: #fff; font-size: 12px; }
    .leg-edit-panel .detail-form textarea { min-height: 70px; }
    .leg-action-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 32px; padding: 7px 12px; border: 1px solid #dbe5f2; border-radius: 7px; background: #fff; color: #2864e8; font-size: 12px; font-weight: 700; line-height: 1; white-space: nowrap; }
    .leg-action-btn.delete { background: #fff; border-color: #f1c8cc; color: #c62828; }
    .leg-action-btn.delete:hover { background: #fff5f5; }
    @media (max-width: 700px) {
        .leg-edit-panel .detail-form { right: auto; left: 0; width: min(440px, 85vw); }
    }
    @media (max-width: 480px) {
        .leg-edit-panel .detail-form { grid-template-columns: 1fr; }
        .leg-edit-panel .detail-form .full { grid-column: auto; }
    }

</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const card = document.getElementById('live-tracking-card');
    const list = document.getElementById('tracking-history-list');
    const statusLine = document.getElementById('live-tracking-status');
    const etaLine = document.getElementById('live-tracking-eta');
    const checkedLine = document.getElementById('live-tracking-checked');
    if (!card || !list) return;

    const url = card.dataset.liveUrl;
    let knownIds = new Set(Array.from(list.querySelectorAll('.tracking-item[data-event-id]'))
        .map(item => item.dataset.eventId));

    function makeText(tag, value) {
        const node = document.createElement(tag);
        node.textContent = value || '';
        return node;
    }

    async function refreshTracking() {
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Tracking refresh unavailable');
            const data = await response.json();
            statusLine.textContent = 'Current shipment status: ' + String(data.shipment_status || '-').replaceAll('_', ' ');
            if (etaLine && data.eta) {
                const etaValue = data.eta.estimated_arrival_at ? new Date(data.eta.estimated_arrival_at).toLocaleString() : 'Not available';
                etaLine.textContent = 'ETA: ' + etaValue + ' · Delay level: ' + (data.eta.delay_level || 'UNKNOWN');
            }

            const freshEvents = (data.events || []).filter(event => !knownIds.has(String(event.id)));
            if (freshEvents.length) {
                window.dispatchEvent(new CustomEvent('cargotrace:tracking-update', { detail: freshEvents }));
                const empty = list.querySelector('.empty-state');
                if (empty) empty.remove();
                freshEvents.forEach(event => {
                    knownIds.add(String(event.id));
                    const item = document.createElement('div');
                    item.className = 'tracking-item';
                    item.dataset.eventId = event.id;
                    item.appendChild(makeText('strong', event.event_type || 'Tracking Update'));
                    item.appendChild(makeText('p', 'Status: ' + String(event.status || '-').replaceAll('_', ' ')));
                    item.appendChild(makeText('p', 'Location: ' + (event.location || '-')));
                    if (event.description) item.appendChild(makeText('p', event.description));
                    item.appendChild(makeText('small', (event.event_at_label || '') + ' · ' + (event.creator || 'System')));
                    list.prepend(item);
                });
            }
            checkedLine.textContent = 'Last checked: ' + new Date(data.checked_at).toLocaleTimeString();
        } catch (error) {
            checkedLine.textContent = 'Live update temporarily unavailable. Retrying automatically…';
        }
    }

    refreshTracking();
    window.setInterval(refreshTracking, 15000);
});
</script>

@endpush

@section('content')

<div class="detail-header">
    <div>
        <h1>Shipment Details</h1>
        <p>Complete information and delivery progress</p>
    </div>

    <div class="detail-actions">
        <a href="{{ route('shipments.index') }}" class="btn secondary">
            ← Back
        </a>

        @if($canManage)
            <a href="{{ route('shipments.edit', $shipment) }}" class="btn">
                Edit Shipment
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert-box alert-success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert-box alert-danger">
        <strong>Please check the following errors:</strong>
        <ul style="margin:8px 0 0;padding-left:20px">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- SHIPMENT SUMMARY --}}
<div class="detail-card">
    <h2>Shipment Overview</h2>

    <div class="detail-grid">
        <div class="detail-item">
            <small>Shipment Number</small>
            <strong>{{ $shipment->shipment_number }}</strong>
        </div>

        <div class="detail-item">
            <small>Current Status</small>
            <span class="status-badge"
                  style="background:{{ $statusColors[$status] ?? '#64748b' }}">
                {{ $statusLabels[$status] ?? str_replace('_', ' ', $status) }}
            </span>
        </div>

        <div class="detail-item">
            <small>Created At</small>
            <strong>{{ $formatDate($shipment->created_at) }}</strong>
        </div>

        <div class="detail-item">
            <small>Cargo Type</small>
            <strong>{{ $shipment->cargo_type ?? '-' }}</strong>
        </div>

        <div class="detail-item">
            <small>Weight</small>
            <strong>
                {{ $shipment->weight_kg !== null ? $shipment->weight_kg . ' kg' : '-' }}
            </strong>
        </div>

        <div class="detail-item">
            <small>Volume</small>
            <strong>
                {{ $shipment->volume_m3 !== null ? $shipment->volume_m3 . ' m³' : '-' }}
            </strong>
        </div>
    </div>
</div>

{{-- CUSTOMER AND CARRIER --}}
<div class="detail-card">
    <h2>Customer & Carrier Information</h2>

    <div class="detail-grid">
        <div class="detail-item">
            <small>Customer Name</small>
            <strong>{{ $shipment->customer?->name ?? '-' }}</strong>
        </div>

        <div class="detail-item">
            <small>Customer Email</small>
            <strong>{{ $shipment->customer?->email ?? '-' }}</strong>
        </div>

        <div class="detail-item">
            <small>Carrier</small>
            <strong>{{ $shipment->carrier?->name ?? '-' }}</strong>
        </div>
    </div>
</div>

{{-- ORIGIN AND DESTINATION --}}
<div class="detail-card">
    <h2>Shipment Route</h2>

    <div class="route-grid">
        <div class="route-box">
            <small>ORIGIN</small>
            <strong>{{ $shipment->origin?->name ?? '-' }}</strong>
            <p style="font-size:11px;color:#8492a7;margin-bottom:0">
                {{ $shipment->origin?->city ?? '-' }}
            </p>
        </div>

        <div class="route-box">
            <small>DESTINATION</small>
            <strong>{{ $shipment->destination?->name ?? '-' }}</strong>
            <p style="font-size:11px;color:#8492a7;margin-bottom:0">
                {{ $shipment->destination?->city ?? '-' }}
            </p>
        </div>
    </div>
</div>

{{-- DELIVERY SCHEDULE --}}
<div class="detail-card">
    <h2>Delivery Schedule & ETA</h2>

    @if($isLate)
        <div class="alert-box alert-warning">
            <strong>Delivery Delayed</strong><br>
            The expected arrival time has passed. Please check the latest tracking event.
        </div>
    @elseif($status === 'DELIVERED')
        <div class="alert-box alert-success">
            Shipment successfully delivered.
        </div>
    @endif

    <div class="detail-grid">
        <div class="detail-item">
            <small>Expected Departure</small>
            <strong>{{ $formatDate($shipment->expected_departure_at) }}</strong>
        </div>

        <div class="detail-item">
            <small>Expected Arrival</small>
            <strong>{{ $formatDate($shipment->expected_arrival_at) }}</strong>
        </div>

        <div class="detail-item">
            <small>Latest Estimated Arrival</small>
            <strong>{{ $formatDate($etaData['estimated_arrival_at'] ?? null) }}</strong>
        </div>
        <div class="detail-item">
            <small>Delay Assessment</small>
            <strong>{{ ($etaData['delay_level'] ?? 'UNKNOWN') }} @if(isset($etaData['delay_hours']) && $etaData['delay_hours'] > 0) · {{ $etaData['delay_hours'] }} hours @endif</strong>
        </div>
        <div class="detail-item">
            <small>Delivery Condition</small>
            <strong>
                @if($status === 'DELIVERED')
                    Delivered
                @elseif($isLate)
                    Delayed
                @else
                    On Schedule
                @endif
            </strong>
        </div>
    </div>
</div>

{{-- CARGO ITEMS --}}
<div class="detail-card">
    <h2>Cargo Items</h2>

    @if($shipment->items->isEmpty())
        <div class="empty-state">No cargo item details available.</div>
    @else
        <div class="detail-table-wrap">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shipment->items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit ?? '-' }}</td>
                            <td>{{ $item->description ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($shipment->description)
        <p style="font-size:11px;color:#78889d;margin-top:15px">
            <strong>Shipment Description:</strong>
            {{ $shipment->description }}
        </p>
    @endif
</div>

{{-- SHIPMENT MAP --}}
<div class="detail-card">
    <h2>Shipment Tracking Map</h2>

    <div id="shipment-detail-map" class="map-container"></div>
    <div class="mm-legend" style="margin-top:10px">
        <span><i style="background:#19a96b"></i>Land</span>
        <span><i style="background:#2864e8"></i>Sea</span>
        <span><i style="background:#8b5cf6"></i>Air</span>
    </div>

    @if($mapPoints->isEmpty())
        <div class="empty-state">
            No location coordinates are available for this shipment yet.
        </div>
    @endif
</div>

{{-- TRACKING HISTORY --}}
<div class="detail-card" id="live-tracking-card"
     data-live-url="{{ route('shipments.tracking.live', $shipment) }}">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
        <h2>Multimodal Transport Legs</h2>
    @if($shipment->legs->isNotEmpty())
        <div class="detail-table-wrap" style="margin-bottom:16px">
            <table class="detail-table" style="min-width:700px">
                <thead><tr><th>Leg</th><th>Mode</th><th>Carrier</th><th>Origin</th><th>Destination</th><th>Status</th><th>Schedule</th>@if($canManage)<th>Actions</th>@endif</tr></thead>
                <tbody>
                @foreach($shipment->legs as $leg)
                    <tr>
                        <td>#{{ $leg->leg_number }}</td>
                        <td>{{ $leg->transport_mode === 'LAND' ? '🚚 Land' : ($leg->transport_mode === 'SEA' ? '🚢 Sea' : '✈ Air') }}</td>
                        <td>{{ $leg->carrier?->name ?? '-' }}</td>
                        <td>{{ $leg->origin?->city ?? $leg->origin?->name ?? '-' }}</td>
                        <td>{{ $leg->destination?->city ?? $leg->destination?->name ?? '-' }}</td>
                        <td>{{ str_replace('_', ' ', $leg->status ?? 'PLANNED') }}</td>
                        <td>{{ $formatDate($leg->expected_departure_at) }}<br>→ {{ $formatDate($leg->expected_arrival_at) }}</td>
                        @if($canManage)
                        <td>
                            <div class="leg-actions">
                                <details class="leg-edit-panel">
                                    <summary class="leg-action-btn">Edit</summary>
                                    <form method="POST" action="{{ route('shipments.legs.update', [$shipment, $leg]) }}" class="detail-form">
                                        @csrf
                                        @method('PATCH')
                                        <div><label>Transport Mode *</label><select name="transport_mode" required><option value="LAND" @selected($leg->transport_mode === 'LAND')>Land</option><option value="SEA" @selected($leg->transport_mode === 'SEA')>Sea</option><option value="AIR" @selected($leg->transport_mode === 'AIR')>Air</option></select></div>
                                        <div><label>Carrier</label><select name="carrier_id"><option value="">No carrier selected</option>@foreach($carriers as $carrier)<option value="{{ $carrier->id }}" @selected($leg->carrier_id == $carrier->id)>{{ $carrier->name }}</option>@endforeach</select></div>
                                        <div><label>Origin *</label><select name="origin_id" required>@foreach($locations as $location)<option value="{{ $location->id }}" @selected($leg->origin_id == $location->id)>{{ $location->name }} — {{ $location->city }}, {{ $location->country }}</option>@endforeach</select></div>
                                        <div><label>Destination *</label><select name="destination_id" required>@foreach($locations as $location)<option value="{{ $location->id }}" @selected($leg->destination_id == $location->id)>{{ $location->name }} — {{ $location->city }}, {{ $location->country }}</option>@endforeach</select></div>
                                        <div><label>Expected Departure</label><input type="datetime-local" name="expected_departure_at" value="{{ $leg->expected_departure_at?->format('Y-m-d\\TH:i') }}"></div>
                                        <div><label>Expected Arrival</label><input type="datetime-local" name="expected_arrival_at" value="{{ $leg->expected_arrival_at?->format('Y-m-d\\TH:i') }}"></div>
                                        <div class="full"><label>Notes</label><textarea name="notes" placeholder="Additional information for this transport stage">{{ $leg->notes }}</textarea></div>
                                        <div class="full"><button type="submit" class="leg-action-btn" style="background:#2864e8;color:#fff;border-color:#2864e8">Save Changes</button></div>
                                    </form>
                                </details>
                                <form method="POST" action="{{ route('shipments.legs.destroy', [$shipment, $leg]) }}" onsubmit="return confirm('Delete transport leg #{{ $leg->leg_number }}? This cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="leg-action-btn delete">Delete</button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p style="color:#8492a7;font-size:12px;margin-bottom:14px">Belum ada tahap transportasi. Tambahkan leg agar shipment multimodal dapat ditampilkan pada dashboard dan peta.</p>
    @endif

    @if($canManage)
        <form method="POST" action="{{ route('shipments.legs.store', $shipment) }}" class="detail-form">
            @csrf
            <div><label for="transport_mode">Transport Mode *</label><select id="transport_mode" name="transport_mode" required><option value="">Select mode</option><option value="LAND" @selected(old('transport_mode') === 'LAND')>Land</option><option value="SEA" @selected(old('transport_mode') === 'SEA')>Sea</option><option value="AIR" @selected(old('transport_mode') === 'AIR')>Air</option></select></div>
            <div><label for="leg_carrier_id">Carrier</label><select id="leg_carrier_id" name="carrier_id"><option value="">No carrier selected</option>@foreach($carriers as $carrier)<option value="{{ $carrier->id }}" @selected(old('carrier_id') == $carrier->id)>{{ $carrier->name }}</option>@endforeach</select></div>
            <div><label for="leg_origin_id">Origin *</label><select id="leg_origin_id" name="origin_id" required><option value="">Select origin</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('origin_id') == $location->id)>{{ $location->name }} — {{ $location->city }}, {{ $location->country }}</option>@endforeach</select></div>
            <div><label for="leg_destination_id">Destination *</label><select id="leg_destination_id" name="destination_id" required><option value="">Select destination</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('destination_id') == $location->id)>{{ $location->name }} — {{ $location->city }}, {{ $location->country }}</option>@endforeach</select></div>
            <div><label for="leg_departure">Expected Departure</label><input id="leg_departure" type="datetime-local" name="expected_departure_at" value="{{ old('expected_departure_at') }}"></div>
            <div><label for="leg_arrival">Expected Arrival</label><input id="leg_arrival" type="datetime-local" name="expected_arrival_at" value="{{ old('expected_arrival_at') }}"></div>
            <div class="full"><label for="leg_notes">Notes</label><textarea id="leg_notes" name="notes" placeholder="Additional information for this transport stage">{{ old('notes') }}</textarea></div>
            <div class="full"><button type="submit" class="dash-button" style="background:#2864e8;color:white;border:0;padding:10px 16px;border-radius:8px;font-weight:700">Add Transport Leg</button></div>
        </form>
    @endif

    <h2>Tracking History</h2>
        <small style="color:#16804a;">● Live updates · checks every 15 seconds</small>
    </div>
    <p id="live-tracking-status" aria-live="polite" style="font-size:13px;color:#64748b;">
        Current shipment status: {{ str_replace('_', ' ', $shipment->current_status) }}
    </p>
    <p id="live-tracking-eta" style="font-size:13px;color:#64748b;">
        ETA: {{ $etaData['estimated_arrival_at']?->format('d M Y H:i') ?? 'Not available' }} · Delay level: {{ $etaData['delay_level'] }}
    </p>
    <div id="tracking-history-list">
    @if($shipment->trackingEvents->isEmpty())
        <div class="empty-state">No tracking events available yet.</div>
    @else
        @foreach($shipment->trackingEvents->sortByDesc('event_at') as $event)
            <div class="tracking-item" data-event-id="{{ $event->id }}">
                <strong>{{ $event->event_type ?? 'Tracking Update' }}</strong>
                <p>Status: {{ str_replace('_', ' ', $event->status ?? '-') }}</p>
                <p>Location: {{ $event->location?->name ?? '-' }}</p>
                @if($event->description)<p>{{ $event->description }}</p>@endif
                <small>{{ $formatDate($event->event_at) }} · {{ $event->creator?->name ?? 'System' }}</small>
            </div>
        @endforeach
    @endif
    </div>
    <small id="live-tracking-checked" style="color:#64748b;">Waiting for next update…</small>
</div>

{{-- ADD TRACKING EVENT --}}
@if($canManage)
    <div class="detail-card">
        <h2>Add Tracking Update</h2>

        <form method="POST"
              action="{{ route('shipments.tracking', $shipment) }}"
              class="detail-form">

            @csrf

            <div>
                <label>Event Type *</label>
                <input type="text"
                       name="event_type"
                       value="{{ old('event_type') }}"
                       placeholder="Example: Shipment departed"
                       required>
            </div>

            <div>
                <label>Shipment Status</label>
                <select name="status">
                    <option value="">Keep current status</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}"
                            @selected(old('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Event Date & Time *</label>
                <input type="datetime-local"
                       name="event_at"
                       value="{{ old('event_at') }}"
                       required>
            </div>

            <div>
                <label>Location</label>
                <select name="location_id">
                    <option value="">Select location</option>
                    @foreach(\App\Models\Location::orderBy('name')->get() as $location)
                        <option value="{{ $location->id }}"
                            @selected(old('location_id') == $location->id)>
                            {{ $location->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label>Latitude</label>
                <input type="number"
                       step="any"
                       name="latitude"
                       value="{{ old('latitude') }}"
                       placeholder="-8.6500">
            </div>

            <div>
                <label>Longitude</label>
                <input type="number"
                       step="any"
                       name="longitude"
                       value="{{ old('longitude') }}"
                       placeholder="115.2167">
            </div>

            <div>
                <label>Estimated Arrival Update</label>
                <input type="datetime-local"
                       name="estimated_arrival_at"
                       value="{{ old('estimated_arrival_at') }}">
            </div>

            <div class="full">
                <label>Description</label>
                <textarea name="description"
                          placeholder="Enter tracking details...">{{ old('description') }}</textarea>
            </div>

            <div class="full">
                <button type="submit" class="btn">
                    Save Tracking Update
                </button>
            </div>
        </form>
    </div>
@endif

{{-- EXCEPTIONS --}}
<div class="detail-card">
    <h2>Shipment Exceptions</h2>

    @if($shipment->exceptions->isEmpty())
        <div class="empty-state">No exceptions reported for this shipment.</div>
    @else
        <div class="detail-table-wrap">
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($shipment->exceptions as $exception)
                        <tr>
                            <td>{{ str_replace('_', ' ', $exception->type) }}</td>
                            <td>{{ $exception->severity }}</td>
                            <td>{{ $exception->status }}</td>
                            <td>{{ $exception->description }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

{{-- REPORT EXCEPTION --}}
@if($canManage)
    <div class="detail-card">
        <h2>Report Shipment Exception</h2>

        <form method="POST"
              action="{{ route('shipments.exceptions', $shipment) }}"
              class="detail-form">

            @csrf

            <div>
                <label>Exception Type *</label>
                <select name="type" required>
                    <option value="">Select exception type</option>
                    <option value="CUSTOMS_HOLD">Customs Hold</option>
                    <option value="WEATHER_DELAY">Weather Delay</option>
                    <option value="PORT_CONGESTION">Port Congestion</option>
                    <option value="DAMAGED">Damaged</option>
                    <option value="MISROUTED">Misrouted</option>
                    <option value="DOCUMENTATION_ISSUE">Documentation Issue</option>
                </select>
            </div>

            <div>
                <label>Severity *</label>
                <select name="severity" required>
                    <option value="">Select severity</option>
                    <option value="LOW">Low</option>
                    <option value="MEDIUM">Medium</option>
                    <option value="HIGH">High</option>
                    <option value="CRITICAL">Critical</option>
                </select>
            </div>

            <div class="full">
                <label>Description *</label>
                <textarea name="description"
                          placeholder="Describe the shipment exception..."
                          required></textarea>
            </div>

            <div class="full">
                <button type="submit" class="btn">
                    Submit Exception
                </button>
            </div>
        </form>
    </div>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('shipment-detail-map');

    if (!mapElement || typeof L === 'undefined') {
        return;
    }

    const points = @json($mapPoints);
    const legs = @json($mapLegs);

    const map = L.map(mapElement, {
        scrollWheelZoom: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const bounds = [];
    const markerKeys = new Set();
    const modeColors = { LAND: '#19a96b', SEA: '#2864e8', AIR: '#8b5cf6' };

    // Draw each recorded transport leg using its transport-mode color.
    legs.forEach(function (leg) {
        const a = [Number(leg.originLat), Number(leg.originLng)];
        const b = [Number(leg.destinationLat), Number(leg.destinationLng)];
        if (!a.every(Number.isFinite) || !b.every(Number.isFinite)) return;

        const mode = String(leg.mode || '').toUpperCase();
        const color = modeColors[mode] || '#64748b';
        const lineStyle = mode === 'SEA'
            ? { dashArray: '10 6' }
            : (mode === 'AIR' ? { dashArray: '2 8', lineCap: 'round' } : {});

        L.polyline([a, b], Object.assign({
            color: color,
            weight: 4,
            opacity: 0.9
        }, lineStyle)).addTo(map).bindPopup(
            String(mode) + ': ' + String(leg.origin || 'Origin') + ' → ' + String(leg.destination || 'Destination')
        );
        bounds.push(a, b);
    });

    window.addEventListener('cargotrace:tracking-update', function (event) {
        (event.detail || []).forEach(function (trackingEvent) {
            const lat = Number(trackingEvent.latitude);
            const lng = Number(trackingEvent.longitude);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;
            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(String(trackingEvent.location || trackingEvent.event_type || 'Tracking update'));
            map.panTo([lat, lng]);
        });
    });

    points.forEach(function (point) {
        const lat = Number(point.lat);
        const lng = Number(point.lng);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }

        const markerKey = lat.toFixed(5) + ',' + lng.toFixed(5);
        if (!markerKeys.has(markerKey)) {
            markerKeys.add(markerKey);
            const marker = L.marker([lat, lng]).addTo(map);
            marker.bindPopup(String(point.name || 'Shipment location'));
        }

        bounds.push([lat, lng]);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, {
            padding: [25, 25],
            maxZoom: 8
        });
    } else {
        map.setView([-2.5, 118], 4);
    }

    setTimeout(function () {
        map.invalidateSize();
    }, 200);
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const card = document.getElementById('live-tracking-card');
    const list = document.getElementById('tracking-history-list');
    const statusLine = document.getElementById('live-tracking-status');
    const etaLine = document.getElementById('live-tracking-eta');
    const checkedLine = document.getElementById('live-tracking-checked');
    if (!card || !list) return;

    const url = card.dataset.liveUrl;
    let knownIds = new Set(Array.from(list.querySelectorAll('.tracking-item[data-event-id]'))
        .map(item => item.dataset.eventId));

    function makeText(tag, value) {
        const node = document.createElement(tag);
        node.textContent = value || '';
        return node;
    }

    async function refreshTracking() {
        try {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Tracking refresh unavailable');
            const data = await response.json();
            statusLine.textContent = 'Current shipment status: ' + String(data.shipment_status || '-').replaceAll('_', ' ');
            if (etaLine && data.eta) {
                const etaValue = data.eta.estimated_arrival_at ? new Date(data.eta.estimated_arrival_at).toLocaleString() : 'Not available';
                etaLine.textContent = 'ETA: ' + etaValue + ' · Delay level: ' + (data.eta.delay_level || 'UNKNOWN');
            }

            const freshEvents = (data.events || []).filter(event => !knownIds.has(String(event.id)));
            if (freshEvents.length) {
                window.dispatchEvent(new CustomEvent('cargotrace:tracking-update', { detail: freshEvents }));
                const empty = list.querySelector('.empty-state');
                if (empty) empty.remove();
                freshEvents.forEach(event => {
                    knownIds.add(String(event.id));
                    const item = document.createElement('div');
                    item.className = 'tracking-item';
                    item.dataset.eventId = event.id;
                    item.appendChild(makeText('strong', event.event_type || 'Tracking Update'));
                    item.appendChild(makeText('p', 'Status: ' + String(event.status || '-').replaceAll('_', ' ')));
                    item.appendChild(makeText('p', 'Location: ' + (event.location || '-')));
                    if (event.description) item.appendChild(makeText('p', event.description));
                    item.appendChild(makeText('small', (event.event_at_label || '') + ' · ' + (event.creator || 'System')));
                    list.prepend(item);
                });
            }
            checkedLine.textContent = 'Last checked: ' + new Date(data.checked_at).toLocaleTimeString();
        } catch (error) {
            checkedLine.textContent = 'Live update temporarily unavailable. Retrying automatically…';
        }
    }

    refreshTracking();
    window.setInterval(refreshTracking, 15000);
});
</script>

@endpush
