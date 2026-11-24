@extends('layouts.app')

@section('title', ucfirst($role) . ' Dashboard')
@section('top-title', ucfirst($role) . ' Dashboard')

@php
    $statusOrder = ['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'];
    $statusLabels = collect($statusOrder)
        ->map(fn ($status) => str_replace('_', ' ', $status))
        ->values();
    $statusValues = collect($statusOrder)
        ->map(fn ($status) => (int) ($statuses[$status] ?? 0))
        ->values();
    $carrierLabels = $carrierDelayStats->keys()->values();
    $carrierDelayRates = $carrierDelayStats->pluck('rate')->values();

    $stats = [
        ['Total Shipments', $total, 'Registered shipments', '▤', ''],
        ['In Transit', $inTransit, 'Currently moving', '➜', ''],
        ['Delivered', $delivered, 'Successfully completed', '✓', ''],
        ['Delayed Shipments', $delayed, 'Past ETA and not delivered', '◷', 'warning'],
        ['Open Exceptions', $exceptions, 'Need attention', '!', 'danger'],
        ['Exception Rate', $exceptionRate . '%', 'Exceptions per shipment', '%', 'danger'],
        ['International', $internationalCount ?? 0, 'Cross-border shipments', '✈', ''],
        ['Multimodal', $multimodalCount ?? 0, 'Multiple transport modes', '▱', ''],
    ];
@endphp

@push('head')
<style>
.dash-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:20px}
.dash-heading h1{font-size:25px;letter-spacing:-.5px;margin:0 0 5px}
.dash-heading p{font-size:12px;color:#8290a4;margin:0}
.dash-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:9px 13px;border:0;border-radius:8px;background:#2864e8;color:#fff;font:700 11px 'DM Sans',sans-serif;cursor:pointer;white-space:nowrap}
.dash-button:hover{filter:brightness(.96)}
.dash-metrics{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px;margin-bottom:15px}
.dash-metric,.dash-panel{background:#fff;border:1px solid #e6edf5;border-radius:13px;box-shadow:0 3px 14px #19385b05}
.dash-metric{min-width:0;padding:15px}
.dash-metric-top{display:flex;align-items:center;justify-content:space-between;gap:7px}
.dash-metric-label{font-size:9px;font-weight:800;color:#8492a7;letter-spacing:.35px;text-transform:uppercase}
.dash-metric-icon{width:26px;height:26px;flex:none;border-radius:8px;display:grid;place-items:center;background:#edf3ff;color:#2864e8;font-size:12px;font-weight:800}
.dash-metric.warning .dash-metric-icon{background:#fff3e4;color:#db7a00}
.dash-metric.danger .dash-metric-icon{background:#fff0f0;color:#d34b4b}
.dash-metric-value{margin-top:8px;color:#172b4d;font-size:27px;font-weight:800;letter-spacing:-.8px;line-height:1.15}
.dash-metric.warning .dash-metric-value{color:#d97706}
.dash-metric-note{margin-top:5px;color:#9aa7b8;font-size:9px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-panels{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,.85fr) minmax(0,1.08fr);gap:13px;margin-bottom:15px}
.dash-panel{padding:16px;min-width:0}
.dash-panel-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:12px}
.dash-panel-heading h2{margin:0 0 4px;color:#203653;font-size:13px}
.dash-panel-heading p{margin:0;color:#9aa7b8;font-size:10px;line-height:1.4}
.dash-panel-value{color:#203653;font-size:13px;font-weight:800}
.dash-chart{height:205px;position:relative}
.dash-map{height:205px;border-radius:9px;background:#eef3f8;z-index:1;cursor:zoom-in}
.dash-map:focus-visible{outline:3px solid #2864e8;outline-offset:2px}
.dash-map-key{display:flex;align-items:center;gap:12px;margin-top:8px;color:#7c8ba1;font-size:9px}
.dash-map-key span{display:inline-flex;align-items:center;gap:5px}
.dash-map-key i{width:7px;height:7px;border-radius:50%}
.dash-origin{background:#3679e8}.dash-destination{background:#16a36b}
.dash-map-hint{margin:7px 0 0;color:#8a98aa;font-size:9px}
.map-modal{display:none;position:fixed;inset:0;z-index:9999;padding:22px;background:#10243acc}
.map-modal.open{display:block}
.map-modal-content{height:100%;display:flex;flex-direction:column;padding:15px;background:#fff;border-radius:13px;box-shadow:0 20px 70px #0004}
.map-modal-heading{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
.map-modal-heading strong{font-size:14px;color:#203653}
#shipment-dashboard-map-fullscreen{flex:1;min-height:0;border-radius:9px;background:#eef3f8}
.dash-section{margin-bottom:15px}
.dash-eta-list{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:0 18px}
.dash-eta-item{padding:11px 0;border-bottom:1px solid #edf1f6}
.dash-eta-row{display:flex;justify-content:space-between;align-items:center;gap:8px}
.dash-eta-id{font-size:11px;font-weight:800;color:#263d5a}
.dash-eta-route,.dash-eta-time{margin-top:5px;color:#78889d;font-size:10px}
.dash-eta-time{font-weight:700;color:#52647b}
.dash-badge{display:inline-block;border-radius:20px;padding:4px 7px;background:#e5f7ef;color:#13805c;font-size:9px;font-weight:800;white-space:nowrap}
.dash-badge.warning{background:#fff3df;color:#ad6500}
.dash-table-wrap{overflow-x:auto}
.dash-table{width:100%;min-width:650px;border-collapse:collapse}
.dash-table th,.dash-table td{padding:11px 10px;border-bottom:1px solid #edf1f6;text-align:left;font-size:11px;white-space:nowrap}
.dash-table th{color:#8492a7;font-size:9px;letter-spacing:.5px;text-transform:uppercase}
.dash-table tbody tr:last-child td{border-bottom:0}
.dash-table-link{color:#2864e8;font-weight:700}
.dash-empty{padding:25px!important;text-align:center!important;color:#8492a7}
.dash-panel-link{color:#2864e8;font-size:10px;font-weight:700}
.dash-notice{padding:10px 0;border-bottom:1px solid #edf1f6}
.dash-notice:last-child{border-bottom:0}

/* Isolated multimodal dashboard UI */
.mm-dashboard{display:grid;grid-template-columns:minmax(270px,.36fr) minmax(0,.64fr);gap:13px;margin:15px 0;width:100%;max-width:100%;box-sizing:border-box}.mm-dashboard>*{min-width:0}.mm-dashboard .dash-panel{min-width:0;overflow:hidden}.mm-map{width:100%;max-width:100%;height:360px;border-radius:10px;background:#edf3f8;z-index:1;box-sizing:border-box}
.mm-left{display:grid;gap:13px;align-content:start}.mm-mode-list{display:grid;gap:8px;margin-top:10px}
.mm-mode-row{display:flex;align-items:center;gap:9px;padding:9px;border-radius:9px;background:#f7f9fc;color:#243b5a;font-size:11px;font-weight:700}
.mm-mode-dot{width:10px;height:10px;border-radius:50%;flex:none}.mm-mode-count{margin-left:auto;font-size:12px}
.mm-filter{display:flex;flex-wrap:wrap;gap:6px}.mm-filter button{border:1px solid #e2eaf4;background:#f6f8fc;color:#52647b;border-radius:8px;padding:7px 10px;font:700 10px 'DM Sans',sans-serif;cursor:pointer}
.mm-filter button.active{background:#e5efff;border-color:#c7dcff;color:#2864e8}.mm-map{height:340px;border-radius:10px;background:#edf3f8;z-index:1}
.mm-list{display:grid;gap:0}.mm-shipment{padding:10px 0;border-bottom:1px solid #edf1f6}.mm-shipment:last-child{border-bottom:0}
.mm-shipment-top{display:flex;justify-content:space-between;gap:8px;align-items:center}.mm-shipment-id{font-size:11px;font-weight:800;color:#203653}
.mm-shipment-route{font-size:10px;color:#7b8ba1;margin-top:4px}.mm-pill{display:inline-block;border-radius:16px;background:#e5efff;color:#2864e8;padding:4px 7px;font-size:9px;font-weight:800}
.mm-timeline{display:flex;align-items:flex-start;gap:0;margin-top:12px;overflow-x:auto}.mm-step{flex:1;min-width:110px;position:relative;padding-top:26px;text-align:center}
.mm-step:before{content:'';position:absolute;top:10px;left:0;right:0;height:3px;background:#d8e1ee}.mm-step:first-child:before{left:50%}.mm-step:last-child:before{right:50%}
.mm-step-dot{position:absolute;top:2px;left:calc(50% - 9px);width:18px;height:18px;border-radius:50%;border:3px solid white;box-shadow:0 0 0 1px #d9e3ef;background:#9aabc0;z-index:2}
.mm-step.done .mm-step-dot{background:#20ae78}.mm-step.current .mm-step-dot{background:#2864e8;box-shadow:0 0 0 4px #dbe8ff}
.mm-step strong{display:block;color:#304865;font-size:10px}.mm-step small{display:block;color:#8292a7;font-size:9px;margin-top:4px;line-height:1.4}
.mm-legend{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;color:#7b8ba1;font-size:9px}.mm-legend span{display:flex;align-items:center;gap:5px}.mm-legend i{display:inline-block;width:14px;height:3px;border-radius:4px}
@media(max-width:1120px){.mm-dashboard{grid-template-columns:1fr}.mm-map{height:300px}}@media(max-width:700px){.mm-dashboard{grid-template-columns:1fr}.mm-map{height:250px}.mm-filter button{padding:6px 8px}}

@media(max-width:1120px){.dash-metrics{grid-template-columns:repeat(4,minmax(0,1fr))}.dash-panels{grid-template-columns:repeat(2,minmax(0,1fr))}.dash-panel-map{grid-column:1/-1}.dash-map{height:250px}}
@media(max-width:700px){.dash-heading{align-items:flex-start;flex-direction:column}.dash-metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.dash-metric{padding:12px}.dash-metric-value{font-size:24px}.dash-panels{grid-template-columns:1fr}.dash-panel-map{grid-column:auto}.dash-map{height:230px}.map-modal{padding:8px}.map-modal-content{padding:10px}.dash-table th,.dash-table td{padding:10px 8px}}
</style>
@endpush

@section('content')
<div class="dash-heading">
    <div>
        <h1>{{ $role === 'customer' ? 'My Dashboard' : 'Dashboard' }}</h1>
        <p>{{ $role === 'customer'
            ? 'Track your shipments, estimated arrivals, and delivery updates.'
            : 'Monitor shipment activity, carrier performance, and delivery operations.' }}</p>
    </div>
    @if(in_array($role, ['admin', 'operator']))
        <a href="{{ route('shipments.create') }}" class="dash-button">+ Create Shipment</a>
    @else
        <a href="{{ route('shipments.index') }}" class="dash-button">My Shipments</a>
    @endif
</div>

<div class="dash-metrics">
    @foreach($stats as $stat)
        <div class="dash-metric {{ $stat[4] }}">
            <div class="dash-metric-top">
                <span class="dash-metric-label">{{ $stat[0] }}</span>
                <span class="dash-metric-icon" aria-hidden="true">{{ $stat[3] }}</span>
            </div>
            <div class="dash-metric-value">{{ $stat[1] }}</div>
            <div class="dash-metric-note">{{ $stat[2] }}</div>
        </div>
    @endforeach
</div>

<div class="dash-panels">
    <section class="dash-panel">
        <div class="dash-panel-heading">
            <div>
                <h2>Shipments by Status</h2>
                <p>Shipment count by current lifecycle status</p>
            </div>
        </div>
        <div class="dash-chart"><canvas id="shipment-status-chart" aria-label="Shipments by status chart"></canvas></div>
    </section>

    <section class="dash-panel">
        <div class="dash-panel-heading">
            <div>
                <h2>Delay Rate by Carrier</h2>
                <p>Percentage of shipments past ETA</p>
            </div>
            <span class="dash-panel-value">{{ $total > 0 ? round(($delayed / $total) * 100) : 0 }}%</span>
        </div>
        @if($carrierLabels->isNotEmpty())
            <div class="dash-chart"><canvas id="carrier-delay-chart" aria-label="Delay rate by carrier chart"></canvas></div>
        @else
            <div class="dash-chart" style="display:grid;place-items:center;color:#9aa7b8;font-size:11px">
                No carrier delay data available.
            </div>
        @endif
    </section>

    <section class="dash-panel dash-panel-map">
        <div class="dash-panel-heading">
            <div>
                <h2>Shipment Map</h2>
                <p>{{ $role === 'customer'
                    ? 'Your shipment origin and destination locations'
                    : 'Shipment origin and destination locations across the workspace' }}</p>
            </div>
            <button type="button" class="dash-button" id="expand-shipment-map" aria-label="Expand shipment map">
                Expand
            </button>
        </div>
        <div id="shipment-dashboard-map" class="dash-map" tabindex="0"
             role="button" aria-label="Click to expand the shipment map"></div>
        <div class="dash-map-key">
            <span><i class="dash-origin"></i>Origin</span>
            <span><i class="dash-destination"></i>Destination</span>
        </div>
        <p class="dash-map-hint">Click the map to expand. Drag to pan and use the controls to zoom.</p>
    </section>
</div>


@if(in_array($role, ['admin', 'operator']))
<section class="mm-dashboard" aria-label="Multimodal dashboard">
 <div class="mm-left">
  <section class="dash-panel"><div class="dash-panel-heading"><div><h2>Multimodal Shipment Distribution</h2><p>Transport legs recorded in CargoTrace</p></div></div>
   <div class="mm-mode-list"><div class="mm-mode-row"><i class="mm-mode-dot" style="background:#19a96b"></i> Land <span class="mm-mode-count">{{ $modeCounts['LAND'] ?? 0 }}</span></div><div class="mm-mode-row"><i class="mm-mode-dot" style="background:#2864e8"></i> Sea <span class="mm-mode-count">{{ $modeCounts['SEA'] ?? 0 }}</span></div><div class="mm-mode-row"><i class="mm-mode-dot" style="background:#8b5cf6"></i> Air <span class="mm-mode-count">{{ $modeCounts['AIR'] ?? 0 }}</span></div></div>
  </section>
  <section class="dash-panel"><div class="dash-panel-heading"><div><h2>Active Multimodal Shipments</h2><p>Shipments using more than one transport mode</p></div></div><div class="mm-list">
   @forelse($activeMultimodal as $shipment)<div class="mm-shipment"><div class="mm-shipment-top"><a class="mm-shipment-id" href="{{ route('shipments.show', $shipment) }}">{{ $shipment->shipment_number }}</a><span class="mm-pill">{{ str_replace('_',' ', $shipment->current_status) }}</span></div><div class="mm-shipment-route">{{ $shipment->origin?->city ?? $shipment->origin?->name ?? 'Origin' }} → {{ $shipment->destination?->city ?? $shipment->destination?->name ?? 'Destination' }}</div><div class="mm-legend">@foreach($shipment->legs->pluck('transport_mode')->unique() as $mode)<span>{{ $mode === 'LAND' ? '🚚 Land' : ($mode === 'SEA' ? '🚢 Sea' : '✈ Air') }}</span>@endforeach</div></div>
   @empty<p class="dash-map-hint">No multimodal shipments yet. Add shipment legs with different modes.</p>@endforelse
  </div></section>
 </div>
 <section class="dash-panel"><div class="dash-panel-heading"><div><h2>Global Shipment Map — Multimodal Tracking</h2><p>Transport legs, transit points, and route mode</p></div></div>
  <div class="mm-filter" id="mm-map-filter"><button type="button" class="active" data-mode="ALL">All Modes</button><button type="button" data-mode="LAND">🚚 Land</button><button type="button" data-mode="SEA">🚢 Sea</button><button type="button" data-mode="AIR">✈ Air</button></div>
  <div id="multimodal-dashboard-map" class="mm-map" style="margin-top:10px;position:relative"></div><div class="mm-legend"><span><i style="background:#19a96b"></i>Land route</span><span><i style="background:#2864e8"></i>Sea route</span><span><i style="background:#8b5cf6"></i>Air route</span></div>
  @php $timelineShipment = $activeMultimodal->first(); @endphp @if($timelineShipment)<div style="margin-top:16px;padding-top:13px;border-top:1px solid #edf1f6"><div class="dash-panel-heading"><div><h2>Tracking Timeline — {{ $timelineShipment->shipment_number }}</h2><p>{{ $timelineShipment->origin?->city ?? 'Origin' }} → {{ $timelineShipment->destination?->city ?? 'Destination' }}</p></div><span class="mm-pill">{{ str_replace('_',' ', $timelineShipment->current_status) }}</span></div><div class="mm-timeline">
   @foreach($timelineShipment->legs as $leg)@php $legDone = in_array(strtoupper($leg->status ?? ''), ['COMPLETED','DELIVERED','ARRIVED']) || $leg->actual_arrival_at; $legCurrent = !$legDone && in_array(strtoupper($leg->status ?? ''), ['IN_TRANSIT','ACTIVE']); @endphp<div class="mm-step {{ $legDone ? 'done' : ($legCurrent ? 'current' : '') }}"><i class="mm-step-dot"></i><strong>{{ $leg->transport_mode }}</strong><small>{{ $leg->origin?->city ?? $leg->origin?->name ?? 'Origin' }} → {{ $leg->destination?->city ?? $leg->destination?->name ?? 'Destination' }}<br>{{ $leg->actual_departure_at?->format('d M H:i') ?? $leg->expected_departure_at?->format('d M H:i') ?? 'Schedule pending' }}</small></div>@endforeach
  </div></div>@endif
 </section>
</section>
@endif

<section class="dash-panel dash-section">
    <div class="dash-panel-heading">
        <div>
            <h2>ETA &amp; Delay Monitoring</h2>
            <p>Upcoming arrivals and shipments that need attention</p>
        </div>
    </div>
    @if($etaShipments->isNotEmpty())
        <div class="dash-eta-list">
            @foreach($etaShipments as $shipment)
                @php
                    $isLate = $shipment->expected_arrival_at
                        && $shipment->expected_arrival_at->isPast()
                        && $shipment->current_status !== 'DELIVERED';
                @endphp
                <div class="dash-eta-item">
                    <div class="dash-eta-row">
                        <span class="dash-eta-id">{{ $shipment->shipment_number }}</span>
                        <span class="dash-badge {{ $isLate ? 'warning' : '' }}">
                            {{ $isLate ? 'Delayed' : 'On schedule' }}
                        </span>
                    </div>
                    <div class="dash-eta-route">
                        {{ $shipment->origin?->city ?? $shipment->origin?->name ?? 'Origin' }}
                        to {{ $shipment->destination?->city ?? $shipment->destination?->name ?? 'Destination' }}
                    </div>
                    <div class="dash-eta-time">
                        ETA: {{ $shipment->expected_arrival_at?->format('d M Y H:i') ?? 'Not set' }}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="dash-map-hint">No upcoming ETA information.</p>
    @endif
</section>

<section class="dash-panel dash-section">
    <div class="dash-panel-heading">
        <div>
            <h2>{{ $role === 'customer' ? 'My Recent Shipments' : 'Recent Shipments' }}</h2>
            <p>Latest shipment activity across CargoTrace</p>
        </div>
        <a class="dash-panel-link" href="{{ route('shipments.index') }}">View all</a>
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
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent as $shipment)
                    <tr>
                        <td><strong>{{ $shipment->shipment_number }}</strong></td>
                        @if($role !== 'customer')<td>{{ $shipment->customer?->name ?? '-' }}</td>@endif
                        <td>{{ $shipment->carrier?->name ?? '-' }}</td>
                        <td>{{ $shipment->origin?->city ?? '-' }}</td>
                        <td>{{ $shipment->destination?->city ?? '-' }}</td>
                        <td>{{ str_replace('_', ' ', $shipment->current_status) }}</td>
                        <td><a class="dash-table-link" href="{{ route('shipments.show', $shipment) }}">Details</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $role === 'customer' ? 6 : 7 }}" class="dash-empty">
                            No shipments available.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

@if($role === 'customer')
    <section class="dash-panel dash-section">
        <div class="dash-panel-heading">
            <div><h2>Recent Notifications</h2><p>Latest updates related to your shipments</p></div>
            <a class="dash-panel-link" href="{{ route('notifications.index') }}">View all</a>
        </div>
        @forelse($notifications as $notification)
            <div class="dash-notice">
                <strong style="font-size:11px">{{ $notification->title }}</strong>
                <p class="dash-map-hint">{{ $notification->message }}</p>
                <small class="dash-map-hint">{{ $notification->created_at->format('d M Y H:i') }}</small>
            </div>
        @empty
            <p class="dash-map-hint">No notifications yet.</p>
        @endforelse
    </section>
@endif

<div id="shipment-map-modal" class="map-modal" aria-hidden="true">
    <div class="map-modal-content" role="dialog" aria-modal="true" aria-labelledby="shipment-map-title">
        <div class="map-modal-heading">
            <strong id="shipment-map-title">Shipment Map</strong>
            <button type="button" class="dash-button" id="close-shipment-map">Close map</button>
        </div>
        <div id="shipment-dashboard-map-fullscreen"></div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const points = @json($mapPoints);
    const modal = document.getElementById('shipment-map-modal');
    const mapElement = document.getElementById('shipment-dashboard-map');
    const expandButton = document.getElementById('expand-shipment-map');
    const closeButton = document.getElementById('close-shipment-map');
    const statusCanvas = document.getElementById('shipment-status-chart');
    const delayCanvas = document.getElementById('carrier-delay-chart');

    if (typeof Chart === 'undefined') {
        console.error('Chart.js failed to load; dashboard charts are unavailable.');
    } else {
        new Chart(statusCanvas, {
            type: 'bar',
            data: {
                labels: @json($statusLabels),
                datasets: [{
                    data: @json($statusValues),
                    backgroundColor: ['#dfe8f3', '#91b5d8', '#b8cce3', '#2864e8', '#76c7a6'],
                    borderRadius: 5,
                    maxBarThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {legend: {display: false}},
                scales: {
                    x: {grid: {display: false}, ticks: {font: {size: 8}, maxRotation: 25, minRotation: 0}},
                    y: {beginAtZero: true, ticks: {precision: 0, font: {size: 9}}, grid: {color: '#edf1f6'}}
                }
            }
        });

        if (delayCanvas) {
            new Chart(delayCanvas, {
                type: 'bar',
                data: {
                    labels: @json($carrierLabels),
                    datasets: [{
                        data: @json($carrierDelayRates),
                        backgroundColor: '#2864e8',
                        borderRadius: 5,
                        maxBarThickness: 22,
                        minBarLength: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {legend: {display: false}},
                    scales: {
                        x: {beginAtZero: true, max: 100, ticks: {callback: value => value + '%', font: {size: 9}}, grid: {color: '#edf1f6'}},
                        y: {grid: {display: false}, ticks: {font: {size: 9}}}
                    }
                }
            });
        }
    }

    if (typeof L === 'undefined') {
        console.error('Leaflet failed to load; shipment maps are unavailable.');
        return;
    }

    function createMap(element, maxZoom) {
        const map = L.map(element, {
            dragging: true,
            scrollWheelZoom: true,
            touchZoom: true
        }).setView([-2.5, 118], 4);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const bounds = [];
        points.forEach(point => {
            const lat = Number(point.lat);
            const lng = Number(point.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

            const marker = L.circleMarker([lat, lng], {
                radius: 7,
                color: '#fff',
                weight: 2,
                fillColor: point.type === 'Origin' ? '#3679e8' : '#16a36b',
                fillOpacity: 1,
                bubblingMouseEvents: false
            }).addTo(map);

            const popup = document.createElement('div');
            const shipment = document.createElement('strong');
            shipment.textContent = String(point.shipment ?? '');
            const location = document.createElement('div');
            location.textContent = String(point.type ?? '') + ': ' + String(point.place ?? '');
            popup.append(shipment, location);
            marker.bindPopup(popup);
            bounds.push([lat, lng]);
        });

        if (bounds.length) map.fitBounds(bounds, {padding: [25, 25], maxZoom});
        return map;
    }

    const mainMap = createMap(mapElement, 10);
    let fullMap = null;
    let previousOverflow = '';

    function openMap() {
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
        previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        if (!fullMap) {
            fullMap = createMap(document.getElementById('shipment-dashboard-map-fullscreen'), 12);
        }

        window.setTimeout(() => fullMap.invalidateSize(), 150);
        closeButton.focus();
    }

    function closeMap() {
        if (!modal.classList.contains('open')) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = previousOverflow;
        expandButton.focus();
    }

    expandButton.addEventListener('click', openMap);
    mainMap.on('click', openMap);
    mapElement.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openMap();
        }
    });
    closeButton.addEventListener('click', closeMap);
    modal.addEventListener('click', function (event) {
        if (event.target === modal) closeMap();
    });
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closeMap();
    });

    window.setTimeout(() => mainMap.invalidateSize(), 150);

    // Multimodal map: consistent mode colors, zoom, and an explicit empty-state for filters.
    const mmElement = document.getElementById('multimodal-dashboard-map');
    if (mmElement) {
        const mmSegments = @json($multimodalSegments);
        const mmMap = L.map(mmElement).setView([12, 115], 4);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(mmMap);

        const colors = { LAND: '#19a96b', SEA: '#2864e8', AIR: '#8b5cf6' };
        const styles = {
            LAND: { weight: 4.5, opacity: .92 },
            SEA: { weight: 4.5, opacity: .9, dashArray: '10 6' },
            AIR: { weight: 4.5, opacity: .92, dashArray: '2 8', lineCap: 'round' }
        };
        const group = L.layerGroup().addTo(mmMap);

        const emptyMessage = document.createElement('div');
        emptyMessage.style.cssText = 'position:absolute;z-index:1000;left:50%;top:50%;transform:translate(-50%,-50%);background:rgba(255,255,255,.95);border:1px solid #dce5ef;border-radius:10px;padding:12px 16px;color:#64748b;font-size:12px;text-align:center;box-shadow:0 3px 12px rgba(20,40,70,.12);display:none;pointer-events:none;';
        mmElement.appendChild(emptyMessage);

        function render(mode) {
            group.clearLayers();
            const bounds = [];
            const markerLocations = new Set();
            const filteredSegments = mmSegments.filter(x => mode === 'ALL' || String(x.mode).toUpperCase() === mode);

            filteredSegments.forEach(x => {
                const a = [Number(x.originLat), Number(x.originLng)];
                const b = [Number(x.destinationLat), Number(x.destinationLng)];
                if (!a.every(Number.isFinite) || !b.every(Number.isFinite)) return;

                const segmentMode = String(x.mode || '').toUpperCase();
                const color = colors[segmentMode] || '#64748b';
                const line = L.polyline([a, b], Object.assign({ color, weight: 4.5, opacity: .9 }, styles[segmentMode] || {})).addTo(group);
                line.bindPopup(String(x.shipment || 'Shipment') + '<br>' + segmentMode + ' · ' + String(x.status || '') + '<br>' + String(x.origin || '') + ' → ' + String(x.destination || ''));

                [[a, x.origin], [b, x.destination]].forEach(function (entry) {
                    const coord = entry[0];
                    const key = coord[0].toFixed(5) + ',' + coord[1].toFixed(5);
                    if (markerLocations.has(key)) return;
                    markerLocations.add(key);
                    L.circleMarker(coord, { radius: 6, color: '#fff', weight: 2, fillColor: color, fillOpacity: 1 })
                        .bindPopup(String(x.shipment || 'Shipment') + ': ' + String(entry[1] || 'Location'))
                        .addTo(group);
                });
                bounds.push(a, b);
            });

            if (bounds.length) {
                emptyMessage.style.display = 'none';
                mmMap.fitBounds(bounds, { padding: [24, 24], maxZoom: 8 });
            } else {
                emptyMessage.textContent = mode === 'ALL'
                    ? 'No transport routes recorded yet.'
                    : 'No ' + mode + ' shipments yet.';
                emptyMessage.style.display = 'block';
                mmMap.setView([12, 115], 4);
            }
        }

        render('ALL');
        document.querySelectorAll('#mm-map-filter button').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('#mm-map-filter button').forEach(b => b.classList.toggle('active', b === this));
                render(this.dataset.mode);
            });
        });
        window.setTimeout(() => mmMap.invalidateSize(), 200);
    }
    
});
</script>
@endpush
