
@extends('layouts.app')

@section('title', 'Shipments')
@section('top-title', 'Shipments')

@push('head')
<style>
    .ship-page {
        color: #172b49;
        width: 100%;
        min-width: 0;
    }

    .ship-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 18px;
        margin-bottom: 22px;
    }

    .ship-head h1 {
        font-size: 25px;
        letter-spacing: -.7px;
        margin: 0 0 5px;
    }

    .ship-head p {
        font-size: 12px;
        color: #8997aa;
        margin: 0;
    }

    .ship-kpis {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .ship-kpi {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 12px;
        padding: 17px 18px;
        box-shadow: 0 3px 12px #172b4d05;
        min-width: 0;
    }

    .ship-kpi small {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: .5px;
        font-weight: 800;
        color: #8b98aa;
    }

    .ship-kpi strong {
        display: block;
        font-size: 27px;
        margin: 7px 0 3px;
        color: #213854;
    }

    .ship-kpi span {
        font-size: 10px;
        color: #9aa7b8;
    }

    /* FILTER AREA */
    .ship-toolbar {
        background: #fff;
        border: 1px solid #e8edf4;
        border-radius: 12px 12px 0 0;
        padding: 17px;
    }

    .ship-search {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        width: 100%;
        align-items: end;
    }

    .ship-filter {
        min-width: 0;
    }

    .ship-filter label {
        display: block;
        margin-bottom: 6px;
        font-size: 10px;
        font-weight: 700;
        color: #718198;
    }

    .ship-filter input,
    .ship-filter select {
        width: 100%;
        height: 40px;
        box-sizing: border-box;
        border: 1px solid #e1e8f0;
        border-radius: 8px;
        padding: 0 11px;
        font-family: inherit;
        font-size: 11px;
        background: #fff;
        color: #334a67;
        outline: none;
        min-width: 0;
    }

    .ship-filter input:focus,
    .ship-filter select:focus {
        border-color: #2864d9;
        box-shadow: 0 0 0 3px #2864d915;
    }

    .ship-filter-search {
        grid-column: span 2;
    }

    .ship-filter-actions {
        display: flex;
        align-items: end;
        gap: 8px;
    }

    .ship-filter-actions .btn {
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 16px;
        white-space: nowrap;
        font-size: 11px;
    }

    .ship-filter-actions .btn.secondary {
        background: #f1f5fb;
        color: #526782;
    }

    .ship-toolbar-bottom {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px solid #edf1f6;
    }

    .ship-toolbar-bottom span {
        font-size: 11px;
        color: #8492a6;
    }

    /* TABS */
    .ship-tabs {
        display: flex;
        gap: 22px;
        align-items: center;
        background: #fff;
        padding: 0 17px;
        border-left: 1px solid #e8edf4;
        border-right: 1px solid #e8edf4;
        border-bottom: 1px solid #edf1f6;
        overflow-x: auto;
    }

    .ship-tabs a {
        font-size: 11px;
        color: #8190a4;
        padding: 14px 2px;
        border-bottom: 2px solid transparent;
        white-space: nowrap;
        text-decoration: none;
    }

    .ship-tabs a.active {
        color: #2864d9;
        border-color: #2864d9;
        font-weight: 800;
    }

    /* TABLE */
    .ship-table-panel {
        margin-top: 0 !important;
        border-radius: 0 0 12px 12px;
        padding: 0 16px 16px;
    }

    .ship-table-scroll {
        width: 100%;
        overflow-x: auto;
    }

    .ship-table {
        min-width: 900px;
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
    }

    .ship-table th {
        padding: 15px 10px;
        text-align: left;
        color: #8997aa;
        font-size: 9px;
        letter-spacing: .5px;
        text-transform: uppercase;
        border-bottom: 1px solid #e9eef5;
        white-space: nowrap;
    }

    .ship-table td {
        padding: 14px 10px;
        border-bottom: 1px solid #f0f3f7;
        color: #62738a;
        vertical-align: middle;
        white-space: nowrap;
    }

    .ship-table tr:last-child td {
        border-bottom: 0;
    }

    .ship-table strong {
        color: #263d5b;
        font-size: 11px;
    }

    .ship-id-sub {
        display: block;
        color: #9aa7b8;
        font-size: 10px;
        margin-top: 4px;
    }

    .ship-status {
        display: inline-block;
        border-radius: 20px;
        padding: 6px 10px;
        font-size: 9px;
        font-weight: 800;
        white-space: nowrap;
        background: #edf4ff;
        color: #3475c5;
    }

    .ship-status.delivered {
        background: #e8f7ef;
        color: #16805b;
    }

    .ship-status.delayed {
        background: #fff2df;
        color: #b66b00;
    }

    .ship-status.pending {
        background: #f0edff;
        color: #7660b6;
    }

    .ship-action {
        display: inline-block;
        border: 0;
        border-radius: 7px;
        padding: 8px 12px;
        background: #edf4ff;
        color: #2864d9;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
        text-decoration: none;
    }

    .ship-action:hover {
        background: #dce9ff;
    }

    .ship-pager {
        padding-top: 16px;
    }

    .ship-empty {
        text-align: center;
        padding: 30px !important;
        color: #91a0b3 !important;
    }

    /* RESPONSIVE */
    @media (max-width: 1100px) {
        .ship-search {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .ship-filter-search {
            grid-column: span 2;
        }
    }

    @media (max-width: 750px) {
        .ship-kpis {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ship-search {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ship-filter-search {
            grid-column: span 2;
        }
    }

    @media (max-width: 500px) {
        .ship-head {
            flex-direction: column;
        }

        .ship-head .btn {
            width: 100%;
            text-align: center;
        }

        .ship-kpis {
            gap: 8px;
        }

        .ship-kpi {
            padding: 12px;
        }

        .ship-search {
            grid-template-columns: 1fr;
        }

        .ship-filter-search {
            grid-column: span 1;
        }

        .ship-filter-actions {
            flex-wrap: wrap;
        }

        .ship-filter-actions .btn {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')

@php
    $status = request('status');

    $statusLabel = fn($value) => match($value) {
        'CREATED' => 'Pending',
        'BOOKED' => 'Booked',
        'PICKED_UP' => 'Picked up',
        'IN_TRANSIT' => 'In transit',
        'DELIVERED' => 'Delivered',
        default => ucfirst(strtolower(str_replace('_', ' ', $value))),
    };
@endphp

<div class="ship-page">

    {{-- PAGE HEADER --}}
    <div class="ship-head">
        <div>
            <h1>Shipments</h1>
            <p>Manage and monitor every shipment, from pickup to delivery.</p>
        </div>

        @if(in_array(auth()->user()->role, ['admin', 'operator']))
            <a class="btn" href="{{ route('shipments.create') }}">
                ＋ Create New Shipment
            </a>
        @endif
    </div>

    {{-- STATISTICS --}}
    <div class="ship-kpis">
        <div class="ship-kpi">
            <small>Total shipments</small>
            <strong>{{ $shipmentStats['total'] ?? $shipments->total() }}</strong>
            <span>All registered shipments</span>
        </div>

        <div class="ship-kpi">
            <small>In transit</small>
            <strong>{{ $shipmentStats['inTransit'] ?? '—' }}</strong>
            <span>Currently moving</span>
        </div>

        <div class="ship-kpi">
            <small>Delivered</small>
            <strong>{{ $shipmentStats['delivered'] ?? '—' }}</strong>
            <span>Successfully completed</span>
        </div>

        <div class="ship-kpi">
            <small>Needs attention</small>
            <strong style="color:#c58a35">
                {{ $shipmentStats['delayed'] ?? '—' }}
            </strong>
            <span>Past ETA and not delivered</span>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="ship-toolbar">
        <form class="ship-search" method="GET" action="{{ route('shipments.index') }}">

            <div class="ship-filter ship-filter-search">
                <label>Search shipment</label>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search shipment, customer or destination..."
                >
            </div>

            <div class="ship-filter">
                <label>Status</label>
                <select name="status">
                    <option value="">All statuses</option>
                    @foreach(['CREATED','BOOKED','PICKED_UP','IN_TRANSIT','DELIVERED'] as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>
                            {{ $statusLabel($st) }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(isset($carriers))
                <div class="ship-filter">
                    <label>Carrier</label>
                    <select name="carrier_id">
                        <option value="">All carriers</option>
                        @foreach($carriers as $carrier)
                            <option value="{{ $carrier->id }}"
                                @selected((string)request('carrier_id') === (string)$carrier->id)>
                                {{ $carrier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if(isset($locations))
                <div class="ship-filter">
                    <label>Origin</label>
                    <select name="origin_id">
                        <option value="">All origins</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}"
                                @selected((string)request('origin_id') === (string)$location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ship-filter">
                    <label>Destination</label>
                    <select name="destination_id">
                        <option value="">All destinations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}"
                                @selected((string)request('destination_id') === (string)$location->id)>
                                {{ $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="ship-filter">
                <label>Created from</label>
                <input
                    type="date"
                    name="date_from"
                    value="{{ request('date_from') }}"
                >
            </div>

            <div class="ship-filter">
                <label>Created to</label>
                <input
                    type="date"
                    name="date_to"
                    value="{{ request('date_to') }}"
                >
            </div>

            <div class="ship-filter">
                <label>Delay status</label>
                <select name="delay_status">
                    <option value="">All delays</option>
                    <option value="delayed" @selected(request('delay_status') === 'delayed')>
                        Delayed
                    </option>
                    <option value="on_time" @selected(request('delay_status') === 'on_time')>
                        On time
                    </option>
                </select>
            </div>

            <div class="ship-filter-actions">
                <button class="btn" type="submit">Apply Filters</button>
                <a class="btn secondary" href="{{ route('shipments.index') }}">Reset</a>
            </div>

        </form>

        <div class="ship-toolbar-bottom">
            <span>Showing {{ $shipments->count() }} of {{ $shipments->total() }} shipments</span>
        </div>
    </div>

    {{-- STATUS TABS --}}
    <div class="ship-tabs">
        <a href="{{ route('shipments.index') }}"
           class="{{ !$status ? 'active' : '' }}">
            All shipments
        </a>

        <a href="{{ route('shipments.index', ['status' => 'IN_TRANSIT']) }}"
           class="{{ $status === 'IN_TRANSIT' ? 'active' : '' }}">
            In transit
        </a>

        <a href="{{ route('shipments.index', ['status' => 'DELIVERED']) }}"
           class="{{ $status === 'DELIVERED' ? 'active' : '' }}">
            Delivered
        </a>

        <a href="{{ route('shipments.index', ['status' => 'CREATED']) }}"
           class="{{ $status === 'CREATED' ? 'active' : '' }}">
            Pending
        </a>
    </div>

    {{-- SHIPMENT TABLE --}}
    <div class="panel ship-table-panel">
        <div class="ship-table-scroll">
            <table class="ship-table">
                <thead>
                    <tr>
                        <th>Shipment ID</th>

                        @if(auth()->user()->role !== 'customer')
                            <th>Customer</th>
                        @endif

                        <th>Carrier</th>
                        <th>Origin</th>
                        <th>Destination</th>
                        <th>Weight</th>
                        <th>ETA</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($shipments as $shipment)

                        @php
                            $late = $shipment->expected_arrival_at
                                && $shipment->expected_arrival_at->isPast()
                                && $shipment->current_status !== 'DELIVERED';

                            $statusClass = $shipment->current_status === 'DELIVERED'
                                ? 'delivered'
                                : ($late
                                    ? 'delayed'
                                    : ($shipment->current_status === 'CREATED' ? 'pending' : ''));
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $shipment->shipment_number }}</strong>
                                <span class="ship-id-sub">{{ $shipment->cargo_type }}</span>
                            </td>

                            @if(auth()->user()->role !== 'customer')
                                <td>{{ $shipment->customer?->name ?? '-' }}</td>
                            @endif

                            <td>{{ $shipment->carrier?->name ?? '-' }}</td>

                            <td>
                                {{ $shipment->origin?->city ?? $shipment->origin?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->destination?->city ?? $shipment->destination?->name ?? '-' }}
                            </td>

                            <td>
                                {{ $shipment->weight_kg !== null
                                    ? number_format((float)$shipment->weight_kg, 1).' kg'
                                    : '—' }}
                            </td>

                            <td>
                                {{ $shipment->expected_arrival_at?->format('d M Y, H:i') ?? 'Not set' }}
                            </td>

                            <td>
                                <span class="ship-status {{ $statusClass }}">
                                    {{ $late ? 'Delayed' : $statusLabel($shipment->current_status) }}
                                </span>
                            </td>

                            <td>
                                <a class="ship-action" href="{{ route('shipments.show', $shipment) }}">
                                    Details →
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->role === 'customer' ? 8 : 9 }}"
                                class="ship-empty">
                                No shipments found. Try changing your search or filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="ship-pager">
            {{ $shipments->links() }}
        </div>
    </div>

</div>
@endsection
