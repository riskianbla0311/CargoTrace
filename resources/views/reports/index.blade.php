@extends('layouts.app')

@section('title', 'Reports')
@section('top-title', 'Shipment Reports')

@section('content')
<style>
    .report-page { color: #142b49; }
    .report-heading { display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:18px; margin-bottom:24px; }
    .report-heading h1 { font-size:27px; font-weight:800; margin:0 0 7px; }
    .report-heading p { color:#71829c; margin:0; font-size:13px; }
    .report-filter { display:flex; flex-wrap:wrap; align-items:end; gap:10px; }
    .report-filter label { display:block; font-size:12px; font-weight:700; margin-bottom:6px; }
    .report-filter input { padding:10px 12px; border:1px solid #dce5f1; border-radius:10px; background:white; color:#142b49; }
    .report-btn { padding:11px 18px; border:0; border-radius:10px; background:#2865ed; color:white; font-weight:700; cursor:pointer; text-decoration:none; font-size:12px; }
    .report-btn.light { background:#edf3ff; color:#2865ed; }
    .report-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:16px; margin-bottom:18px; }
    .report-card { background:white; border:1px solid #e4ebf5; border-radius:16px; padding:20px; box-shadow:0 5px 18px rgba(25,55,95,.035); min-width:0; }
    .report-card .small-label { color:#71829c; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.4px; }
    .report-card .big-number { font-size:29px; font-weight:800; margin-top:10px; color:#142b49; }
    .report-card .sub-label { color:#8b9bb2; font-size:12px; margin-top:5px; }
    .report-icon { width:43px; height:43px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:21px; margin-bottom:15px; background:#edf3ff; color:#2865ed; }
    .report-two { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px; }
    .report-card h3 { font-size:16px; font-weight:800; margin:0 0 20px; }
    .status-row { margin:16px 0; }
    .status-info { display:flex; justify-content:space-between; gap:10px; font-size:12px; margin-bottom:8px; color:#526782; }
    .status-track { height:9px; border-radius:20px; background:#edf1f7; overflow:hidden; }
    .status-fill { height:100%; border-radius:20px; background:#2865ed; }
    .report-table { width:100%; border-collapse:collapse; font-size:12px; }
    .report-table th { text-align:left; color:#8190a7; font-size:10px; text-transform:uppercase; letter-spacing:.4px; padding:12px 10px; background:#f7f9fc; }
    .report-table td { padding:14px 10px; border-bottom:1px solid #edf1f6; color:#425671; }
    .report-table tr:last-child td { border-bottom:0; }
    .report-table-wrap { overflow-x:auto; }
    .report-badge { display:inline-block; border-radius:20px; padding:5px 9px; background:#eaf1ff; color:#2865ed; font-size:10px; font-weight:800; }
    .report-export { display:flex; justify-content:flex-end; margin:0 0 12px; }
    .report-empty { color:#8b9bb2; padding:20px 10px; text-align:center; font-size:12px; }
    @media(max-width:1000px) { .report-grid { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media(max-width:650px) { .report-grid,.report-two { grid-template-columns:1fr; } .report-heading { align-items:flex-start; } }
</style>

<div class="report-page">
    <div class="report-heading">
        <div>
            <h1>Shipment Reports</h1>
            <p>Operational reports and shipment performance overview.</p>
        </div>

        <form method="GET" action="{{ route('reports.index') }}" class="report-filter">
            <div>
                <label>From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div>
                <label>To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            <button class="report-btn" type="submit">Apply Filter</button>
            <a class="report-btn light" href="{{ route('reports.index') }}">Reset</a>
        </form>
    </div>

    <div class="report-grid">
        <div class="report-card">
            <div class="report-icon">▣</div>
            <div class="small-label">Total Shipments</div>
            <div class="big-number">{{ $total }}</div>
            <div class="sub-label">All recorded shipments</div>
        </div>
        <div class="report-card">
            <div class="report-icon">🚚</div>
            <div class="small-label">In Transit</div>
            <div class="big-number">{{ $inTransit }}</div>
            <div class="sub-label">Currently on the way</div>
        </div>
        <div class="report-card">
            <div class="report-icon" style="background:#e5f8ef;color:#10a66a;">✓</div>
            <div class="small-label">Delivered</div>
            <div class="big-number">{{ $delivered }}</div>
            <div class="sub-label">Successfully delivered</div>
        </div>
        <div class="report-card">
            <div class="report-icon" style="background:#fff2e6;color:#f08a25;">◷</div>
            <div class="small-label">Delayed</div>
            <div class="big-number">{{ $delayed }}</div>
            <div class="sub-label">Past expected arrival</div>
        </div>
        <div class="report-card">
            <div class="report-icon">◎</div>
            <div class="small-label">Delivery Rate</div>
            <div class="big-number">{{ $deliveryRate }}%</div>
            <div class="sub-label">Delivered / total shipments</div>
        </div>
        <div class="report-card">
            <div class="report-icon" style="background:#fff2e6;color:#f08a25;">⌛</div>
            <div class="small-label">Delay Rate</div>
            <div class="big-number">{{ $delayRate }}%</div>
            <div class="sub-label">Delayed / total shipments</div>
        </div>
        <div class="report-card">
            <div class="report-icon" style="background:#fff3e8;color:#e78a24;">!</div>
            <div class="small-label">Open Exceptions</div>
            <div class="big-number">{{ $openExceptions }}</div>
            <div class="sub-label">Open or investigating</div>
        </div>
        <div class="report-card">
            <div class="report-icon">▣</div>
            <div class="small-label">Not Yet Delivered</div>
            <div class="big-number">{{ $total - $delivered }}</div>
            <div class="sub-label">Remaining shipments</div>
        </div>
    </div>

    <div class="report-two">
        <div class="report-card">
            <h3>Shipment Status Overview</h3>
            @foreach($statusStats as $status => $count)
                @php
                    $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                @endphp
                <div class="status-row">
                    <div class="status-info">
                        <strong>{{ str_replace('_', ' ', $status) }}</strong>
                        <span>{{ $count }} shipments ({{ $percentage }}%)</span>
                    </div>
                    <div class="status-track">
                        <div class="status-fill" style="width:{{ $percentage }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="report-card">
            <h3>Delivery Performance</h3>
            <div style="margin-bottom:25px;">
                <div class="status-info">
                    <strong>Delivery Rate</strong>
                    <strong>{{ $deliveryRate }}%</strong>
                </div>
                <div class="status-track">
                    <div class="status-fill" style="width:{{ min(100, $deliveryRate) }}%;background:#16b981;"></div>
                </div>
            </div>
            <div>
                <div class="status-info">
                    <strong>Delay Rate</strong>
                    <strong>{{ $delayRate }}%</strong>
                </div>
                <div class="status-track">
                    <div class="status-fill" style="width:{{ min(100, $delayRate) }}%;background:#f59e0b;"></div>
                </div>
            </div>
            <div style="margin-top:28px;padding:15px;border-radius:12px;background:#f4f7fc;color:#71829c;font-size:12px;">
                Performance metrics are calculated from shipments within the selected date range.
            </div>
        </div>
    </div>

    <div class="report-card" style="margin-bottom:18px;">
        <h3>Carrier Performance</h3>
        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Carrier</th>
                        <th>Total Shipments</th>
                        <th>In Transit</th>
                        <th>Delivered</th>
                        <th>Delivery Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($carrierStats as $carrier)
                        <tr>
                            <td><strong>{{ $carrier['name'] }}</strong></td>
                            <td>{{ $carrier['total'] }}</td>
                            <td>{{ $carrier['in_transit'] }}</td>
                            <td>{{ $carrier['delivered'] }}</td>
                            <td>
                                <span class="report-badge">
                                    {{ $carrier['total'] > 0 ? round(($carrier['delivered'] / $carrier['total']) * 100, 1) : 0 }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="report-empty">No carrier data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="report-card">
        <h3>Shipment Report Details</h3>
        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Shipment Number</th>
                        <th>Customer</th>
                        <th>Carrier</th>
                        <th>Status</th>
                        <th>Expected Arrival</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shipments as $shipment)
                        <tr>
                            <td><strong>{{ $shipment->shipment_number }}</strong></td>
                            <td>{{ $shipment->customer?->name ?? '-' }}</td>
                            <td>{{ $shipment->carrier?->name ?? '-' }}</td>
                            <td><span class="report-badge">{{ str_replace('_', ' ', $shipment->current_status) }}</span></td>
                            <td>{{ $shipment->expected_arrival_at?->format('d M Y H:i') ?? '-' }}</td>
                            <td><a href="{{ route('shipments.show', $shipment) }}" style="color:#2865ed;font-weight:700;text-decoration:none;">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="report-empty">No shipment data available.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection