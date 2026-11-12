@extends('layouts.app')

@section('title', ucfirst($role) . ' Dashboard')
@section('top-title', ucfirst($role) . ' Dashboard')

@php
    $roleTitles = [
        'admin' => 'Dashboard',
        'operator' => 'Dashboard',
        'customer' => 'My Dashboard',
    ];

    $roleDescriptions = [
        'admin' => 'Your overview of all shipments, carrier performance, and delivery operations.',
        'operator' => 'Monitor shipment movement, delivery schedules, and operational exceptions.',
        'customer' => 'Track your shipments, estimated arrivals, and the latest delivery updates.',
    ];

    $statusOrder = ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];
    $statusLabels = collect($statusOrder)->map(fn ($status) => str_replace('_', ' ', $status))->values();
    $statusValues = collect($statusOrder)->map(fn ($status) => (int) ($statuses[$status] ?? 0))->values();

    $carrierLabels = $role === 'customer'
        ? ['On time', 'Delayed']
        : $carrierDelayStats->keys()->values();
    $carrierValues = $role === 'customer'
        ? [max(0, $total - $delayed), $delayed]
        : $carrierDelayStats->pluck('rate')->values();

    $delayRate = $total > 0 ? round(($delayed / $total) * 100, 1) : 0;
    $mapSubtitle = $role === 'customer'
        ? 'Your shipment origin and destination locations'
        : 'Shipment origin and destination locations across the workspace';
@endphp

@push('head')
<style>
.dash-head{display:flex;justify-content:space-between;align-items:flex-end;gap:18px;margin-bottom:22px}
.dash-head h1{font-size:27px;letter-spacing:-.7px;margin:0 0 6px;color:#172b4d}
.dash-head p{margin:0;color:#7b8ba3;font-size:12px}
.dash-actions{display:flex;gap:8px;flex-wrap:wrap}
.dash-metrics{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:13px;margin-bottom:16px}
.dash-metric{background:#fff;border:1px solid #e6edf5;border-radius:13px;padding:16px 17px;min-width:0;box-shadow:0 3px 14px #19385b05}
.dash-metric-top{display:flex;align-items:center;justify-content:space-between;gap:8px}
.dash-metric-label{font-size:10px;font-weight:800;color:#8492a7;letter-spacing:.45px;text-transform:uppercase}
.dash-metric-icon{width:27px;height:27px;border-radius:8px;display:grid;place-items:center;background:#edf3ff;color:#2864e8;font-size:13px}
.dash-metric-value{font-size:28px;font-weight:800;color:#172b4d;letter-spacing:-.8px;margin-top:9px}
.dash-metric-note{font-size:10px;color:#9aa7b8;margin-top:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(270px,.75fr) minmax(300px,1.1fr);gap:14px;margin-bottom:15px}
.dash-panel{background:#fff;border:1px solid #e6edf5;border-radius:13px;padding:17px;min-width:0;box-shadow:0 3px 14px #19385b05}
.dash-panel-title{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:13px}
.dash-panel-title h2{font-size:13px;margin:0 0 4px;color:#203653}
.dash-panel-title p{font-size:10px;color:#9aa7b8;margin:0}
.dash-chart{height:205px;position:relative}
.dash-map{height:205px;border-radius:9px;background:#eef3f8;z-index:1}
.map-key{display:flex;gap:12px;align-items:center;font-size:9px;color:#7c8ba1;white-space:nowrap}
.map-key span{display:flex;align-items:center;gap:5px}
.map-key i{width:7px;height:7px;border-radius:50%;display:inline-block}
.map-origin{background:#3679e8}.map-destination{background:#16a36b}
.map-hint{font-size:10px;color:#8a98aa;line-height:1.5;margin-top:8px}
.dash-eta-list{display:grid;gap:0}
.dash-eta-item{padding:10px 0;border-bottom:1px solid #edf1f6}
.dash-eta-item:first-child{padding-top:1px}
.dash-eta-item:last-child{border-bottom:0}
.dash-eta-row{display:flex;justify-content:space-between;align-items:center;gap:8px}
.dash-eta-id{font-size:10px;font-weight:800;color:#263d5a}
.dash-eta-route{font-size:10px;color:#78889d;margin-top:5px}
.dash-eta-time{font-size:10px;font-weight:700;color:#263d5a;margin-top:6px}
.dash-eta-warning{font-size:9px;color:#b56c08;background:#fff6e6;padding:5px 7px;border-radius:5px;margin-top:6px;display:inline-block}
.dash-table-panel{margin-top:15px}
.dash-table-wrap{overflow-x:auto}
.dash-table{width:100%;border-collapse:collapse;min-width:740px;font-size:10px}
.dash-table th{padding:11px 10px;text-align:left;color:#8b98aa;font-size:9px;letter-spacing:.5px;text-transform:uppercase;border-bottom:1px solid #eaf0f6;white-space:nowrap}
.dash-table td{padding:12px 10px;border-bottom:1px solid #f0f3f7;color:#51627a;vertical-align:middle}
.dash-table tr:last-child td{border-bottom:0}
.dash-table strong{color:#263d5a}
.dash-status{display:inline-block;border-radius:20px;padding:4px 7px;background:#edf3ff;color:#2864c8;font-size:9px;font-weight:800;white-space:nowrap}
.dash-status.good{background:#e8f7ef;color:#16805b}
.dash-status.warn{background:#fff3df;color:#b66b00}
.dash-status.bad{background:#fff0f0;color:#c43c3c}
.dash-table-actions{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px}
.dash-table-actions h2{font-size:13px;margin:0 0 4px}
.dash-table-actions p{font-size:10px;color:#9aa7b8;margin:0}
.dash-empty{font-size:10px;color:#91a0b3;text-align:center;padding:22px}
.dash-panel-link{font-size:10px;color:#2864e8;font-weight:800;white-space:nowrap}
.dash-notice{margin-top:14px}
@media(max-width:1250px){.dash-layout{grid-template-columns:1fr 1fr}.dash-map-panel{grid-column:1/-1}.dash-map{height:260px}}
@media(max-width:950px){.dash-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}.dash-layout{grid-template-columns:1fr 1fr}}
@media(max-width:700px){.dash-head{align-items:flex-start;flex-direction:column}.dash-metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.dash-metric{padding:13px}.dash-metric-value{font-size:24px}.dash-layout{grid-template-columns:1fr}.dash-map-panel{grid-column:auto}.dash-map{height:230px}.dash-panel{padding:14px}.dash-table-actions{align-items:flex-start;flex-direction:column}}
</style>
@endpush

@section('content')
<div class="dash-head">
    <div>
        <h1>{{ $roleTitles[$role] ?? 'Dashboard' }}</h1>
        <p>Hello, {{ auth()->user()->name }}. {{ $roleDescriptions[$role] ?? 'Shipment visibility overview.' }}</p>
    </div>
    <div class="dash-actions">
        @if(in_array($role, ['admin', 'operator']))
            <a class="btn" href="{{ route('shipments.create') }}">＋ Create shipment</a>
        @else
            <a class="btn" href="{{ route('shipments.index') }}">View my shipments</a>
        @endif
    </div>
</div>

{{-- KPI cards --}}
<div class="dash-metrics">
    <div class="dash-metric">
        <div class="dash-metric-top"><span class="dash-metric-label">{{ $role === 'customer' ? 'My shipments' : 'Total shipments' }}</span><span class="dash-metric-icon">▤</span></div>
        <div class="dash-metric-value">{{ $total }}</div>
        <div class="dash-metric-note">Registered shipments</div>
    </div>
    <div class="dash-metric">
        <div class="dash-metric-top"><span class="dash-metric-label">In transit</span><span class="dash-metric-icon">➜</span></div>
        <div class="dash-metric-value">{{ $inTransit }}</div>
        <div class="dash-metric-note">Currently moving</div>
    </div>
    <div class="dash-metric">
        <div class="dash-metric-top"><span class="dash-metric-label">Delivered</span><span class="dash-metric-icon">✓</span></div>
        <div class="dash-metric-value">{{ $delivered }}</div>
        <div class="dash-metric-note">Successfully completed</div>
    </div>
    <div class="dash-metric">
        <div class="dash-metric-top"><span class="dash-metric-label">Delayed shipments</span><span class="dash-metric-icon" style="background:#fff4e5;color:#d97706">◷</span></div>
        <div class="dash-metric-value" style="color:#d97706">{{ $delayed }}</div>
        <div class="dash-metric-note">Past ETA and not delivered</div>
    </div>
    <div class="dash-metric">
        <div class="dash-metric-top"><span class="dash-metric-label">{{ $role === 'customer' ? 'Shipment issues' : 'Open exceptions' }}</span><span class="dash-metric-icon" style="background:#fff0f0;color:#c43c3c">!</span></div>
        <div class="dash-metric-value">{{ $exceptions }}</div>
        <div class="dash-metric-note">Need attention</div>
    </div>
</div>

{{-- Charts and shipment map --}}
<div class="dash-layout">
    <div class="dash-panel">
        <div class="dash-panel-title">
            <div><h2>Shipments by Status</h2><p>Shipment count by current lifecycle status</p></div>
        </div>
        <div class="dash-chart"><canvas id="shipmentStatusChart"></canvas></div>
    </div>

    <div class="dash-panel">
        <div class="dash-panel-title">
            <div><h2>{{ $role === 'customer' ? 'My Delivery Health' : 'Delay Rate by Carrier' }}</h2><p>{{ $role === 'customer' ? 'On-time versus delayed deliveries' : 'Percentage of shipments past ETA' }}</p></div>
            <strong style="font-size:15px;color:#263d5a">{{ $delayRate }}%</strong>
        </div>
        <div class="dash-chart"><canvas id="delayRateChart"></canvas></div>
        @if($role !== 'customer' && $carrierDelayStats->isEmpty())
            <div class="map-hint">Carrier delay statistics will appear after shipments have a carrier and an ETA.</div>
        @endif
    </div>

    <div class="dash-panel dash-map-panel">
        <div class="dash-panel-title">
            <div><h2>Shipment Map</h2><p>{{ $mapSubtitle }}</p></div>
            <span class="dash-status good">● Map</span>
        </div>
        <div id="shipment-dashboard-map" class="dash-map"></div>
        <div class="map-key" style="margin-top:9px">
            <span><i class="map-origin"></i> Origin</span>
            <span><i class="map-destination"></i> Destination</span>
        </div>
        @if($mapPoints->isEmpty())
            <div class="map-hint">No map coordinates are available yet. Add latitude and longitude to the origin and destination locations to show shipment markers.</div>
        @endif
    </div>
</div>

{{-- ETA monitoring --}}
<div class="dash-panel">
    <div class="dash-panel-title">
        <div><h2>ETA & Delay Monitoring</h2><p>Upcoming estimated arrival times and shipments that may need attention</p></div>
        <a class="dash-panel-link" href="{{ route('shipments.index') }}">View all shipments →</a>
    </div>
    @if($etaShipments->isEmpty())
        <div class="dash-empty">No upcoming ETA information is available yet.</div>
    @else
        <div class="dash-eta-list">
            @foreach($etaShipments as $shipment)
                @php
                    $isLate = $shipment->expected_arrival_at && $shipment->expected_arrival_at->isPast();
                @endphp
                <div class="dash-eta-item">
                    <div class="dash-eta-row">
                        <span class="dash-eta-id">{{ $shipment->shipment_number }}</span>
                        <span class="dash-status {{ $isLate ? 'warn' : 'good' }}">{{ $isLate ? 'Delayed' : 'On schedule' }}</span>
                    </div>
                    <div class="dash-eta-route">
                        {{ $shipment->origin?->city ?? $shipment->origin?->name ?? 'Origin' }}
                        → {{ $shipment->destination?->city ?? $shipment->destination?->name ?? 'Destination' }}
                        @if($role !== 'customer' && $shipment->carrier)
                            · {{ $shipment->carrier->name }}
                        @endif
                    </div>
                    <div class="dash-eta-time">Estimated arrival: {{ $shipment->expected_arrival_at->format('d M Y, H:i') }}</div>
                    @if($isLate)
                        <div class="dash-eta-warning">ETA has passed — check the latest tracking event.</div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Recent shipments --}}
<div class="dash-panel dash-table-panel">
    <div class="dash-table-actions">
        <div><h2>{{ $role === 'customer' ? 'My Recent Shipments' : 'Recent Shipments' }}</h2><p>{{ $role === 'customer' ? 'Latest updates for your deliveries.' : 'Latest shipment activity across CargoTrace.' }}</p></div>
        <a class="btn secondary" href="{{ route('shipments.index') }}">View all</a>
    </div>
    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead>
                <tr>
                    <th>Shipment ID</th>
                    @if($role !== 'customer')<th>Customer</th>@endif
                    <th>Carrier</th>
                    <th>Origin</th>
                    <th>Destination</th>
                    <th>ETA</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $shipment)
                    @php
                        $late = $shipment->expected_arrival_at
                            && $shipment->expected_arrival_at->isPast()
                            && $shipment->current_status !== 'DELIVERED';
                        $statusClass = $shipment->current_status === 'DELIVERED'
                            ? 'good'
                            : ($late ? 'warn' : '');
                    @endphp
                    <tr>
                        <td><strong>{{ $shipment->shipment_number }}</strong></td>
                        @if($role !== 'customer')<td>{{ $shipment->customer?->name ?? '-' }}</td>@endif
                        <td>{{ $shipment->carrier?->name ?? '-' }}</td>
                        <td>{{ $shipment->origin?->city ?? $shipment->origin?->name ?? '-' }}</td>
                        <td>{{ $shipment->destination?->city ?? $shipment->destination?->name ?? '-' }}</td>
                        <td>
                            {{ $shipment->expected_arrival_at?->format('d M Y H:i') ?? 'Not set' }}
                            @if($late)<br><small style="color:#b66b00">Past ETA</small>@endif
                        </td>
                        <td><span class="dash-status {{ $statusClass }}">{{ str_replace('_', ' ', $shipment->current_status) }}</span></td>
                        <td><a class="dash-panel-link" href="{{ route('shipments.show', $shipment) }}">Details →</a></td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $role === 'customer' ? 7 : 8 }}" class="dash-empty">No shipments available yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($role === 'customer')
    <div class="dash-panel dash-notice">
        <div class="dash-panel-title">
            <div><h2>Recent Notifications</h2><p>Latest updates related to your shipments</p></div>
            <a class="dash-panel-link" href="{{ route('notifications.index') }}">View all →</a>
        </div>
        @forelse($notifications as $notification)
            <div style="padding:10px 0;border-bottom:1px solid #edf1f6">
                <strong style="font-size:11px">{{ $notification->title }}</strong>
                <div class="map-hint">{{ $notification->message }}</div>
                <small class="map-hint">{{ $notification->created_at->format('d M Y H:i') }}</small>
            </div>
        @empty
            <div class="dash-empty">No notifications yet.</div>
        @endforelse
    </div>
@endif
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Status chart
    const statusCanvas = document.getElementById('shipmentStatusChart');
    if (statusCanvas && typeof Chart !== 'undefined') {
        new Chart(statusCanvas, {
            type: 'bar',
            data: {
                labels: @json($statusLabels),
                datasets: [{
                    label: 'Shipments',
                    data: @json($statusValues),
                    backgroundColor: ['#a9c9e8', '#8eb8e0', '#6e9fd0', '#2864e8', '#71c7a4'],
                    borderRadius: 5,
                    maxBarThickness: 34
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#edf1f6' } },
                    x: { grid: { display: false }, ticks: { font: { size: 9 } } }
                }
            }
        });
    }

    // Carrier delay / customer delivery health chart
    const delayCanvas = document.getElementById('delayRateChart');
    if (delayCanvas && typeof Chart !== 'undefined') {
        const delayLabels = @json($carrierLabels);
        const delayValues = @json($carrierValues);
        @if($role === 'customer')
            const delayColors = ['#16a36b', '#e6a23c'];
        @else
            const delayColors = ['#2864e8', '#16a36b', '#e6a23c', '#8b79cf', '#e17b70', '#78a9c9'];
        @endif

        new Chart(delayCanvas, {
            type: 'doughnut',
            data: {
                labels: delayLabels,
                datasets: [{
                    data: delayValues,
                    backgroundColor: delayColors,
                    borderWidth: 0,
                    hoverOffset: 5
                }]
            },
            options: {
                maintainAspectRatio: false,
                cutout: '67%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 8, boxHeight: 8, padding: 10, font: { size: 9 } }
                    }
                }
            }
        });
    }

    // Shipment map
    const mapElement = document.getElementById('shipment-dashboard-map');
    if (mapElement && typeof L !== 'undefined') {
        const points = @json($mapPoints);
        const map = L.map(mapElement, { scrollWheelZoom: false });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const bounds = [];
        points.forEach(function (point) {
            const lat = Number(point.lat);
            const lng = Number(point.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

            const color = point.type === 'Origin' ? '#3679e8' : '#16a36b';
            const marker = L.circleMarker([lat, lng], {
                radius: 7,
                color: '#ffffff',
                weight: 2,
                fillColor: color,
                fillOpacity: 1
            }).addTo(map);

            marker.bindPopup(
                '<strong>' + String(point.shipment) + '</strong><br>' +
                String(point.type) + ': ' + String(point.place)
            );
            bounds.push([lat, lng]);
        });

        if (bounds.length) {
            map.fitBounds(bounds, { padding: [22, 22], maxZoom: 10 });
        } else {
            map.setView([-2.5, 118], 4);
        }

        setTimeout(function () { map.invalidateSize(); }, 180);
    }
});
</script>
@endpush
