
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
</style>
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

    @if($mapPoints->isEmpty())
        <div class="empty-state">
            No location coordinates are available for this shipment yet.
        </div>
    @endif
</div>

{{-- TRACKING HISTORY --}}
<div class="detail-card">
    <h2>Tracking History</h2>

    @if($shipment->trackingEvents->isEmpty())
        <div class="empty-state">No tracking events available yet.</div>
    @else
        @foreach($shipment->trackingEvents->sortByDesc('event_at') as $event)
            <div class="tracking-item">
                <strong>
                    {{ $event->event_type ?? 'Tracking Update' }}
                </strong>

                <p>
                    Status:
                    {{ str_replace('_', ' ', $event->status ?? '-') }}
                </p>

                <p>
                    Location:
                    {{ $event->location?->name ?? '-' }}
                </p>

                @if($event->description)
                    <p>{{ $event->description }}</p>
                @endif

                <small>
                    {{ $formatDate($event->event_at) }}
                    ·
                    {{ $event->creator?->name ?? 'System' }}
                </small>
            </div>
        @endforeach
    @endif
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

    const map = L.map(mapElement, {
        scrollWheelZoom: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    const bounds = [];

    points.forEach(function (point) {
        const lat = Number(point.lat);
        const lng = Number(point.lng);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return;
        }

        const marker = L.marker([lat, lng]).addTo(map);

        marker.bindPopup(
            String(point.name || 'Shipment location')
        );

        bounds.push([lat, lng]);
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, {
            padding: [25, 25],
            maxZoom: 12
        });
    } else {
        map.setView([-2.5, 118], 4);
    }

    setTimeout(function () {
        map.invalidateSize();
    }, 200);
});
</script>
@endpush
