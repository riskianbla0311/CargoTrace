@extends('layouts.app')

@section('title', 'Tracking')
@section('top-title', 'Shipment Tracking')

@section('content')
<style>
.tracking-page { color:#142b49; }
.tracking-heading { margin-bottom:22px; }
.tracking-heading h1 { font-size:26px; font-weight:800; margin:0 0 6px; }
.tracking-heading p { color:#8190a7; font-size:13px; margin:0; }
.tracking-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:15px; margin-bottom:20px; }
.tracking-card,.tracking-panel { background:#fff; border:1px solid #e5ebf3; border-radius:15px; padding:20px; box-shadow:0 5px 18px rgba(25,55,95,.035); }
.tracking-card .label { color:#8190a7; font-size:10px; font-weight:800; }
.tracking-card .number { font-size:28px; font-weight:800; margin-top:10px; }
.tracking-card .foot { color:#8b9bb2; font-size:11px; margin-top:5px; }
.tracking-icon { width:40px; height:40px; border-radius:12px; display:flex; align-items:center; justify-content:center; background:#edf3ff; color:#2865ed; font-size:20px; margin-bottom:13px; }
.tracking-panel { margin-bottom:18px; }
.tracking-panel h2 { font-size:16px; font-weight:800; margin:0 0 17px; }
.tracking-search { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:18px; }
.tracking-search input,.tracking-search select { padding:11px 13px; border:1px solid #dfe7f1; border-radius:9px; background:white; color:#142b49; font-size:12px; }
.tracking-search input { flex:1; min-width:190px; }
.tracking-search button,.tracking-search a { border:0; border-radius:9px; padding:11px 17px; background:#2865ed; color:white; font-weight:700; font-size:12px; text-decoration:none; cursor:pointer; }
.tracking-search a { background:#edf3ff; color:#2865ed; }
.tracking-table { width:100%; border-collapse:collapse; font-size:12px; }
.tracking-table th { padding:13px 10px; text-align:left; background:#f7f9fc; color:#8190a7; font-size:10px; }
.tracking-table td { padding:14px 10px; border-bottom:1px solid #edf1f6; color:#526782; }
.tracking-badge { display:inline-block; padding:5px 9px; border-radius:20px; background:#eaf1ff; color:#2865ed; font-size:10px; font-weight:800; }
.tracking-view { display:inline-block; padding:7px 11px; border-radius:8px; background:#edf3ff; color:#2865ed; font-weight:700; text-decoration:none; font-size:11px; }
.tracking-empty { text-align:center; padding:25px; color:#8b9bb2; }
.tracking-detail-grid { display:grid; grid-template-columns:1.5fr 1fr; gap:18px; }
.tracking-info-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:12px; }
.tracking-info { background:#f7f9fc; padding:13px; border-radius:11px; min-width:0; }
.tracking-info small { display:block; color:#8190a7; font-size:10px; margin-bottom:6px; }
.tracking-info strong { font-size:12px; overflow-wrap:anywhere; }
.tracking-progress { display:flex; align-items:flex-start; justify-content:space-between; gap:5px; margin:28px 0; }
.tracking-step { flex:1; text-align:center; position:relative; font-size:10px; color:#8190a7; }
.tracking-step:not(:last-child):after { content:''; position:absolute; height:4px; background:#e3eaf5; top:14px; left:60%; width:80%; z-index:0; }
.tracking-step.done:not(:last-child):after { background:#2865ed; }
.tracking-dot { position:relative; z-index:1; width:30px; height:30px; margin:0 auto 9px; border-radius:50%; border:3px solid #dce5f2; background:white; }
.tracking-step.done .tracking-dot,.tracking-step.current .tracking-dot { background:#2865ed; border-color:#2865ed; box-shadow:0 0 0 4px #eaf1ff; }
.tracking-step strong { display:block; font-size:10px; color:#526782; }
.tracking-step.current strong { color:#2865ed; }
.timeline-item { position:relative; padding:0 0 23px 27px; border-left:2px solid #dce7f8; margin-left:9px; }
.timeline-item:last-child { border-left-color:transparent; padding-bottom:0; }
.timeline-dot { position:absolute; left:-8px; top:0; width:14px; height:14px; background:#2865ed; border:3px solid #eaf1ff; border-radius:50%; }
.timeline-item strong { font-size:12px; }
.timeline-item p { font-size:11px; color:#8190a7; margin:5px 0; }
.timeline-item small { font-size:10px; color:#8190a7; }
@media(max-width:900px) { .tracking-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .tracking-detail-grid { grid-template-columns:1fr; } }
@media(max-width:600px) { .tracking-grid { grid-template-columns:1fr 1fr; } .tracking-info-grid { grid-template-columns:1fr; } }
</style>

<div class="tracking-page">
    <div class="tracking-heading">
        <h1>Shipment Tracking</h1>
        <p>
            @if(auth()->user()->role === 'customer')
                Pantau status dan perjalanan pengiriman milik kamu.
            @elseif(auth()->user()->role === 'operator')
                Pantau aktivitas dan perkembangan pengiriman operasional.
            @else
                Pantau seluruh aktivitas tracking pengiriman CargoTrace.
            @endif
        </p>
    </div>

    <div class="tracking-grid">
        <div class="tracking-card">
            <div class="tracking-icon">▣</div>
            <div class="label">TOTAL TRACKING EVENTS</div>
            <div class="number">{{ $totalEvents }}</div>
            <div class="foot">Total riwayat aktivitas</div>
        </div>
        <div class="tracking-card">
            <div class="tracking-icon">🚚</div>
            <div class="label">IN TRANSIT EVENTS</div>
            <div class="number">{{ $inTransit }}</div>
            <div class="foot">Aktivitas dalam perjalanan</div>
        </div>
        <div class="tracking-card">
            <div class="tracking-icon" style="background:#e5f8ef;color:#10a66a;">✓</div>
            <div class="label">DELIVERED EVENTS</div>
            <div class="number">{{ $delivered }}</div>
            <div class="foot">Aktivitas pengiriman selesai</div>
        </div>
        <div class="tracking-card">
            <div class="tracking-icon" style="background:#fff2e6;color:#e78a25;">◷</div>
            <div class="label">DELAYED SHIPMENTS</div>
            <div class="number">{{ $delayed }}</div>
            <div class="foot">Pengiriman melewati estimasi</div>
        </div>
    </div>

    <div class="tracking-panel">
        <h2>Tracking History</h2>
        <form method="GET" action="{{ route('tracking.index') }}" class="tracking-search">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nomor shipment atau event...">
            <select name="status">
                <option value="">Semua Status</option>
                @foreach(['CREATED','BOOKED','PICKED_UP','IN_TRANSIT','DELIVERED'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_',' ',$status) }}</option>
                @endforeach
            </select>
            <button type="submit">Filter</button>
            <a href="{{ route('tracking.index') }}">Reset</a>
        </form>

        <div style="overflow-x:auto">
            <table class="tracking-table">
                <thead>
                    <tr>
                        <th>Shipment</th>
                        @if(auth()->user()->role !== 'customer')<th>Customer</th>@endif
                        <th>Event</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Date & Time</th>
                        @if(auth()->user()->role !== 'customer')<th>Updated By</th>@endif
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($trackingEvents as $event)
                        <tr>
                            <td><strong>{{ $event->shipment?->shipment_number ?? '-' }}</strong></td>
                            @if(auth()->user()->role !== 'customer')
                                <td>{{ $event->shipment?->customer?->name ?? '-' }}</td>
                            @endif
                            <td>{{ $event->event_type }}</td>
                            <td><span class="tracking-badge">{{ str_replace('_',' ',$event->status ?? 'N/A') }}</span></td>
                            <td>{{ $event->location?->name ?? 'Location not set' }}</td>
                            <td>{{ $event->event_at?->format('d M Y, H:i') ?? '-' }}</td>
                            @if(auth()->user()->role !== 'customer')
                                <td>{{ $event->creator?->name ?? '-' }}</td>
                            @endif
                            <td>
                                @if($event->shipment)
                                    <a href="{{ route('tracking.index', array_merge(request()->query(), ['shipment_id' => $event->shipment->id])) }}" class="tracking-view">View Detail</a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ auth()->user()->role === 'customer' ? 6 : 8 }}"><div class="tracking-empty">Belum ada riwayat tracking yang tersedia.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:18px">{{ $trackingEvents->links() }}</div>
    </div>

    @if($selectedShipment)
        @php
            $steps = [
                'CREATED' => 'Created',
                'BOOKED' => 'Booked',
                'PICKED_UP' => 'Picked Up',
                'IN_TRANSIT' => 'In Transit',
                'DELIVERED' => 'Delivered',
            ];
            $statuses = array_keys($steps);
            $currentIndex = array_search($selectedShipment->current_status, $statuses, true);
            $currentIndex = $currentIndex === false ? -1 : $currentIndex;
        @endphp

        <div class="tracking-detail-grid">
            <div class="tracking-panel">
                <h2>Tracking Detail — {{ $selectedShipment->shipment_number }}</h2>
                <span class="tracking-badge">{{ str_replace('_',' ',$selectedShipment->current_status) }}</span>

                <div class="tracking-progress">
                    @foreach($steps as $key => $label)
                        <div class="tracking-step {{ array_search($key, $statuses, true) <= $currentIndex ? 'done' : '' }} {{ $key === $selectedShipment->current_status ? 'current' : '' }}">
                            <div class="tracking-dot"></div>
                            <strong>{{ $label }}</strong>
                        </div>
                    @endforeach
                </div>

                <div class="tracking-info-grid">
                    <div class="tracking-info"><small>Shipment Number</small><strong>{{ $selectedShipment->shipment_number }}</strong></div>
                    <div class="tracking-info"><small>Customer</small><strong>{{ $selectedShipment->customer?->name ?? '-' }}</strong></div>
                    <div class="tracking-info"><small>Carrier</small><strong>{{ $selectedShipment->carrier?->name ?? '-' }}</strong></div>
                    <div class="tracking-info"><small>Current Location</small><strong>{{ $selectedShipment->trackingEvents->last()?->location?->name ?? '-' }}</strong></div>
                    <div class="tracking-info"><small>Origin</small><strong>{{ $selectedShipment->origin?->name ?? '-' }}</strong></div>
                    <div class="tracking-info"><small>Destination</small><strong>{{ $selectedShipment->destination?->name ?? '-' }}</strong></div>
                    <div class="tracking-info"><small>Expected Arrival (ETA)</small><strong>{{ $selectedShipment->expected_arrival_at?->format('d M Y, H:i') ?? '-' }}</strong></div>
                    <div class="tracking-info"><small>Current Status</small><strong>{{ str_replace('_',' ',$selectedShipment->current_status) }}</strong></div>
                </div>

                @if(in_array(auth()->user()->role, ['admin','operator'], true))
                    <div style="margin-top:18px">
                        <a href="{{ route('shipments.show', $selectedShipment) }}" class="tracking-view">Open Shipment & Update Tracking</a>
                    </div>
                @endif
            </div>

            <div class="tracking-panel">
                <h2>Tracking Timeline</h2>
                @forelse($selectedShipment->trackingEvents->sortByDesc('event_at') as $event)
                    <div class="timeline-item">
                        <div class="timeline-dot"></div>
                        <strong>{{ str_replace('_',' ',$event->event_type) }}</strong>
                        <p>{{ $event->description ?: 'Aktivitas pengiriman tercatat.' }}</p>
                        <small>{{ $event->event_at?->format('d M Y, H:i') ?? '-' }} · {{ $event->location?->name ?? 'Lokasi tidak tersedia' }}</small>
                    </div>
                @empty
                    <div class="tracking-empty">Belum ada timeline untuk shipment ini.</div>
                @endforelse
            </div>
        </div>
    @endif
</div>
@endsection