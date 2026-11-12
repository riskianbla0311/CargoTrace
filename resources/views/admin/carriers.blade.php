@extends('layouts.app')
@section('title','Carriers')
@section('top-title','Carriers')

@push('head')
<style>
.carrier-page{color:#172b49}
.carrier-head{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:19px}
.carrier-head h1{font-size:25px;letter-spacing:-.7px;margin:0 0 5px}
.carrier-head p{font-size:11px;color:#8997aa;margin:0}
.carrier-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:15px}
.carrier-kpi{background:#fff;border:1px solid #e8edf4;border-radius:11px;padding:14px 16px;box-shadow:0 3px 12px #172b4d05}
.carrier-kpi small{font-size:9px;text-transform:uppercase;letter-spacing:.5px;font-weight:800;color:#8b98aa}
.carrier-kpi strong{display:block;font-size:24px;letter-spacing:-.7px;margin:5px 0 2px;color:#213854}
.carrier-kpi span{font-size:9px;color:#9aa7b8}
.carrier-toolbar{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-bottom:13px}
.carrier-search{display:flex;gap:8px;flex:1}
.carrier-search input{height:36px;max-width:340px;width:100%;border:1px solid #e1e8f0;border-radius:7px;padding:0 11px;font:11px 'DM Sans'}
.carrier-toolbar-right{display:flex;align-items:center;gap:8px}
.carrier-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}
.carrier-card{background:#fff;border:1px solid #e8edf4;border-radius:12px;padding:15px 15px 13px;box-shadow:0 3px 12px #172b4d05;min-width:0}
.carrier-card-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:11px}
.carrier-logo{min-width:62px;height:32px;padding:0 9px;border-radius:5px;background:#fff4e5;color:#bd4c32;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:900;letter-spacing:-.4px}
.carrier-logo.blue{background:#edf5ff;color:#2864b9}.carrier-logo.green{background:#e8f7f1;color:#16805b}.carrier-logo.purple{background:#f2edff;color:#7251a6}.carrier-logo.gray{background:#f0f3f7;color:#56677d}
.carrier-active{font-size:9px;font-weight:800;color:#16805b;background:#e8f7ef;border-radius:20px;padding:5px 8px}
.carrier-inactive{font-size:9px;font-weight:800;color:#a15d42;background:#fff0e9;border-radius:20px;padding:5px 8px}
.carrier-card h3{font-size:13px;margin:0 0 4px;color:#263d5b}
.carrier-code{font-size:9px;color:#91a0b3;margin-bottom:13px}
.carrier-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;border-top:1px solid #edf1f6;padding-top:11px}
.carrier-metrics small{display:block;font-size:8px;color:#9aa7b8;margin-bottom:5px}
.carrier-metrics strong{font-size:16px;color:#263d5b}
.carrier-metrics .rate{color:#16805b}
.carrier-contact{border-top:1px solid #f0f3f7;margin-top:12px;padding-top:10px;display:flex;flex-direction:column;gap:5px;font-size:9px;color:#8291a5;overflow-wrap:anywhere}
.carrier-contact a{color:#68809b}
.carrier-add{margin-top:16px}
.carrier-add summary{cursor:pointer;list-style:none;display:flex;align-items:center;justify-content:space-between;font-size:12px;font-weight:800;color:#263d5b}
.carrier-add summary::-webkit-details-marker{display:none}
.carrier-add[open] summary{margin-bottom:15px}
.carrier-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
.carrier-form .field input{font-size:11px}
.carrier-form .full{grid-column:1/-1}
.carrier-pagination{margin-top:15px}
.carrier-empty{grid-column:1/-1;background:#fff;border:1px dashed #dce5ef;border-radius:12px;text-align:center;padding:30px;color:#8b9aaf;font-size:11px}
@media(max-width:1050px){.carrier-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:850px){.carrier-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:580px){.carrier-head{flex-direction:column}.carrier-head .btn{width:100%}.carrier-grid{grid-template-columns:1fr}.carrier-toolbar{align-items:stretch;flex-direction:column}.carrier-search input{max-width:none}.carrier-form{grid-template-columns:1fr}.carrier-form .full{grid-column:auto}}
</style>
@endpush

@section('content')
@php
    $logoColors = ['blue','purple','gray','green'];
@endphp
<div class="carrier-page">
    <div class="carrier-head">
        <div>
            <h1>Carriers</h1>
            <p>Your delivery network, connected and in control.</p>
        </div>
        <a class="btn" href="#add-carrier" onclick="document.getElementById('add-carrier').open=true">＋ Add New Carrier</a>
    </div>

    <div class="carrier-kpis">
        <div class="carrier-kpi"><small>Partner carriers</small><strong>{{ $carrierStats['total'] }}</strong><span>Registered delivery partners</span></div>
        <div class="carrier-kpi"><small>Active fleet</small><strong>{{ $carrierStats['active'] }}</strong><span>Currently active carriers</span></div>
        <div class="carrier-kpi"><small>Assigned shipments</small><strong>{{ $carrierStats['shipments'] }}</strong><span>Shipments in CargoTrace</span></div>
        <div class="carrier-kpi"><small>Delivery success</small><strong>{{ number_format($carrierStats['successRate'],1) }}%</strong><span>Delivered from all shipments</span></div>
    </div>

    <div class="carrier-toolbar">
        <form class="carrier-search" method="GET" action="{{ route('admin.carriers') }}">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="⌕  Search by carrier name or code">
            <button class="btn secondary" type="submit">Search</button>
        </form>
        <div class="carrier-toolbar-right"><span class="muted">{{ $carriers->total() }} carriers</span></div>
    </div>

    <div class="carrier-grid">
        @forelse($carriers as $index => $carrier)
            @php
                $logoClass = $logoColors[$index % count($logoColors)];
                $initials = strtoupper(substr($carrier->code ?: $carrier->name,0,12));
                $success = $carrier->shipments_count > 0
                    ? round(($carrier->delivered_shipments_count / $carrier->shipments_count) * 100,1)
                    : 0;
            @endphp
            <article class="carrier-card">
                <div class="carrier-card-top">
                    <div class="carrier-logo {{ $logoClass }}">{{ $initials }}</div>
                    @if($carrier->is_active)<span class="carrier-active">● Active</span>@else<span class="carrier-inactive">● Inactive</span>@endif
                </div>
                <h3>{{ $carrier->name }}</h3>
                <div class="carrier-code">{{ $carrier->code }} · Delivery partner</div>
                <div class="carrier-metrics">
                    <div><small>Assigned</small><strong>{{ $carrier->shipments_count }}</strong></div>
                    <div><small>Delivered</small><strong>{{ $carrier->delivered_shipments_count }}</strong></div>
                    <div><small>Success rate</small><strong class="rate">{{ number_format($success,1) }}%</strong></div>
                </div>
                <div class="carrier-contact">
                    @if($carrier->contact_email)<span>✉ &nbsp;{{ $carrier->contact_email }}</span>@endif
                    @if($carrier->phone)<span>☎ &nbsp;{{ $carrier->phone }}</span>@endif
                    @if($carrier->website)<a href="{{ $carrier->website }}" target="_blank" rel="noopener">↗ &nbsp;{{ $carrier->website }}</a>@endif
                </div>
                <details style="margin-top:12px">
                    <summary style="cursor:pointer;color:#2864e8;font-size:10px;font-weight:800">Edit carrier details</summary>
                    <form method="POST" action="{{ route('admin.carriers.update',$carrier) }}" class="carrier-form" style="margin-top:10px">
                        @csrf @method('PATCH')
                        <div class="field"><label>Name</label><input name="name" value="{{ $carrier->name }}" required></div>
                        <div class="field"><label>Code</label><input name="code" value="{{ $carrier->code }}" required></div>
                        <div class="field"><label>Email</label><input type="email" name="contact_email" value="{{ $carrier->contact_email }}"></div>
                        <div class="field"><label>Phone</label><input name="phone" value="{{ $carrier->phone }}"></div>
                        <div class="field full"><label>Website</label><input type="url" name="website" value="{{ $carrier->website }}"></div>
                        <div class="field full"><label>Status</label><select name="is_active"><option value="1" @selected($carrier->is_active)>Active</option><option value="0" @selected(!$carrier->is_active)>Inactive</option></select></div>
                        <div class="full"><button class="btn secondary">Save changes</button></div>
                    </form>
                </details>
            </article>
        @empty
            <div class="carrier-empty">No carriers registered yet. Add your first delivery partner.</div>
        @endforelse
    </div>
    <div class="carrier-pagination">{{ $carriers->links() }}</div>

    <details class="panel carrier-add" id="add-carrier">
        <summary><span>＋ Add a new carrier</span><span class="muted">Open form ▾</span></summary>
        <form method="POST" action="{{ route('admin.carriers.store') }}" class="carrier-form">
            @csrf
            <div class="field"><label>Carrier name</label><input name="name" value="{{ old('name') }}" placeholder="e.g. DHL Express" required></div>
            <div class="field"><label>Carrier code</label><input name="code" value="{{ old('code') }}" placeholder="e.g. DHL" required></div>
            <div class="field"><label>Contact email</label><input type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="support@carrier.com"></div>
            <div class="field"><label>Phone</label><input name="phone" value="{{ old('phone') }}" placeholder="+62..."></div>
            <div class="field full"><label>Website</label><input type="url" name="website" value="{{ old('website') }}" placeholder="https://example.com"></div>
            <div class="full"><button class="btn" type="submit">Save Carrier</button></div>
        </form>
    </details>
</div>
@endsection
