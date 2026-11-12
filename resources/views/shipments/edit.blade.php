@extends('layouts.app')
@section('title', 'Update shipment')
@section('top-title', 'Update shipment')

@section('content')
<div class="pagehead">
    <div><h1>Update shipment</h1><div class="muted">Update shipment information without changing its lifecycle status.</div></div>
    <a class="btn secondary" href="{{ route('shipments.show', $shipment) }}">Back to detail</a>
</div>

<form class="panel" method="POST" action="{{ route('shipments.update', $shipment) }}">
    @csrf
    @method('PUT')
    <div class="formgrid">
        <div class="field"><label>Shipment number</label><input name="shipment_number" value="{{ old('shipment_number', $shipment->shipment_number) }}" required></div>
        <div class="field"><label>Customer</label><select name="customer_id" required>@foreach($customers as $customer)<option value="{{ $customer->id }}" @selected(old('customer_id', $shipment->customer_id)==$customer->id)>{{ $customer->name }} · {{ $customer->email }}</option>@endforeach</select></div>
        <div class="field"><label>Carrier</label><select name="carrier_id" required>@foreach($carriers as $carrier)<option value="{{ $carrier->id }}" @selected(old('carrier_id', $shipment->carrier_id)==$carrier->id)>{{ $carrier->name }}</option>@endforeach</select></div>
        <div class="field"><label>Cargo type</label><input name="cargo_type" value="{{ old('cargo_type', $shipment->cargo_type) }}" required></div>
        <div class="field"><label>Origin</label><select name="origin_id" required>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('origin_id', $shipment->origin_id)==$location->id)>{{ $location->name }} — {{ $location->city }}</option>@endforeach</select></div>
        <div class="field"><label>Destination</label><select name="destination_id" required>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('destination_id', $shipment->destination_id)==$location->id)>{{ $location->name }} — {{ $location->city }}</option>@endforeach</select></div>
        <div class="field"><label>Weight (kg)</label><input type="number" step="0.01" min="0" name="weight_kg" value="{{ old('weight_kg', $shipment->weight_kg) }}"></div>
        <div class="field"><label>Volume (m³)</label><input type="number" step="0.001" min="0" name="volume_m3" value="{{ old('volume_m3', $shipment->volume_m3) }}"></div>
        <div class="field"><label>Expected departure</label><input type="datetime-local" name="expected_departure_at" value="{{ old('expected_departure_at', $shipment->expected_departure_at?->format('Y-m-d\TH:i')) }}" required></div>
        <div class="field"><label>Expected arrival</label><input type="datetime-local" name="expected_arrival_at" value="{{ old('expected_arrival_at', $shipment->expected_arrival_at?->format('Y-m-d\TH:i')) }}" required></div>
        <div class="field"><label>Item name</label><input name="item_name" value="{{ old('item_name', $shipment->items->first()?->item_name) }}" placeholder="Optional cargo item"></div>
        <div class="field"><label>Item quantity</label><input type="number" min="1" name="item_quantity" value="{{ old('item_quantity', $shipment->items->first()?->quantity ?? 1) }}"></div>
        <div class="field"><label>Unit</label><input name="item_unit" value="{{ old('item_unit', $shipment->items->first()?->unit ?? 'pcs') }}"></div>
        <div class="field"><label>Item description</label><input name="item_description" value="{{ old('item_description', $shipment->items->first()?->description) }}"></div>
        <div class="field full"><label>Description</label><textarea name="description" rows="3">{{ old('description', $shipment->description) }}</textarea></div>
    </div>
    <p class="muted">Current status: <strong>{{ $shipment->current_status }}</strong>. Status hanya dapat berubah melalui Tracking Management.</p>
    <button class="btn" style="margin-top:12px">Save changes</button>
</form>
@endsection
