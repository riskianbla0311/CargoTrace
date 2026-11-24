<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\ShipmentException;
use App\Services\ShipmentService;
use App\Services\TrackingService;
use App\Services\EtaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    private function visible(Request $request)
    {
        $query = Shipment::with(['customer', 'carrier', 'origin', 'destination']);

        if ($request->user()->role === 'customer') {
            $query->where('customer_id', $request->user()->id);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $baseQuery = $this->visible($request);

        // KPI mengikuti cakupan data: Customer hanya melihat shipment miliknya.
        $shipmentStats = [
            'total' => (clone $baseQuery)->count(),
            'inTransit' => (clone $baseQuery)->where('current_status', 'IN_TRANSIT')->count(),
            'delivered' => (clone $baseQuery)->where('current_status', 'DELIVERED')->count(),
            'delayed' => (clone $baseQuery)
                ->whereNotNull('expected_arrival_at')
                ->where('expected_arrival_at', '<', now())
                ->where('current_status', '!=', 'DELIVERED')
                ->count(),
        ];

        $query = $this->visible($request);

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($builder) use ($term) {
                $builder->where('shipment_number', 'like', '%'.$term.'%')
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$term.'%'))
                    ->orWhereHas('origin', fn ($origin) => $origin->where('city', 'like', '%'.$term.'%'))
                    ->orWhereHas('destination', fn ($destination) => $destination->where('city', 'like', '%'.$term.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }
        if ($request->filled('origin_id')) $query->where('origin_id', $request->origin_id);
        if ($request->filled('destination_id')) $query->where('destination_id', $request->destination_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->input('delay_status') === 'delayed') {
            $query->whereNotNull('expected_arrival_at')->where('expected_arrival_at', '<', now())->where('current_status', '!=', 'DELIVERED');
        } elseif ($request->input('delay_status') === 'on_time') {
            $query->where(function ($builder) {
                $builder->whereNull('expected_arrival_at')->orWhere('expected_arrival_at', '>=', now())->orWhere('current_status', 'DELIVERED');
            });
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        if (!in_array($sort, ['created_at', 'expected_arrival_at', 'shipment_number', 'current_status'], true)) $sort = 'created_at';
        $shipments = $query->orderBy($sort, $direction)->paginate(10)->withQueryString();
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        return view('shipments.index', compact('shipments', 'shipmentStats', 'carriers', 'locations'));
    }

    public function create()
    {
        abort_unless(in_array(auth()->user()->role, ['admin', 'operator'], true), 403);

        $customers = User::where('role', 'customer')->orderBy('name')->get();
        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::orderBy('name')->get();

        return view('shipments.create', compact('customers', 'carriers', 'locations'));
    }

    public function store(Request $request, ShipmentService $service)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);

        $data = $request->validate([
            'shipment_number' => 'required|string|max:60|unique:shipments',
            'customer_id' => 'required|exists:users,id',
            'carrier_id' => 'required|exists:carriers,id',
            'origin_id' => 'required|exists:locations,id',
            'destination_id' => 'required|exists:locations,id',
            'cargo_type' => 'required|string|max:120',
            'weight_kg' => 'nullable|numeric|min:0',
            'volume_m3' => 'nullable|numeric|min:0',
            'expected_departure_at' => 'required|date',
            'expected_arrival_at' => 'required|date|after:expected_departure_at',
            'description' => 'nullable|string',
            'item_name' => 'nullable|string|max:150',
            'item_quantity' => 'nullable|integer|min:1',
            'item_unit' => 'nullable|string|max:30',
            'item_description' => 'nullable|string',
        ]);

        $item = array_intersect_key($data, array_flip(['item_name', 'item_quantity', 'item_unit', 'item_description']));
        unset($data['item_name'], $data['item_quantity'], $data['item_unit'], $data['item_description']);
        $data['current_status'] = 'CREATED';
        $shipment = DB::transaction(function () use ($service, $data, $item, $request) {
            $created = $service->create($data, $request->user()->id);
            if (!empty($item['item_name'])) {
                $created->items()->create([
                    'item_name' => $item['item_name'],
                    'quantity' => $item['item_quantity'] ?? 1,
                    'unit' => $item['item_unit'] ?? 'pcs',
                    'description' => $item['item_description'] ?? null,
                ]);
            }
            return $created;
        });

        return redirect()->route('shipments.show', $shipment)->with('success', 'Shipment berhasil dibuat.');
    }

    public function edit(Request $request, Shipment $shipment)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);
        $customers = User::where('role', 'customer')->orderBy('name')->get();
        $carriers = Carrier::where('is_active', true)->orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $shipment->load('items');

        return view('shipments.edit', compact('shipment', 'customers', 'carriers', 'locations'));
    }

    public function update(Request $request, Shipment $shipment, ShipmentService $service)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);

        $data = $request->validate([
            'shipment_number' => ['required', 'string', 'max:60', Rule::unique('shipments', 'shipment_number')->ignore($shipment->id)],
            'customer_id' => 'required|exists:users,id',
            'carrier_id' => 'required|exists:carriers,id',
            'origin_id' => 'required|exists:locations,id',
            'destination_id' => 'required|exists:locations,id',
            'cargo_type' => 'required|string|max:120',
            'weight_kg' => 'nullable|numeric|min:0',
            'volume_m3' => 'nullable|numeric|min:0',
            'expected_departure_at' => 'required|date',
            'expected_arrival_at' => 'required|date|after:expected_departure_at',
            'description' => 'nullable|string',
            'item_name' => 'nullable|string|max:150',
            'item_quantity' => 'nullable|integer|min:1',
            'item_unit' => 'nullable|string|max:30',
            'item_description' => 'nullable|string',
        ]);

        $item = array_intersect_key($data, array_flip(['item_name', 'item_quantity', 'item_unit', 'item_description']));
        unset($data['item_name'], $data['item_quantity'], $data['item_unit'], $data['item_description']);

        DB::transaction(function () use ($service, $shipment, $data, $item, $request) {
            $service->update($shipment, $data, $request->user()->id);
            if (!empty($item['item_name'])) {
                $shipment->items()->delete();
                $shipment->items()->create([
                    'item_name' => $item['item_name'],
                    'quantity' => $item['item_quantity'] ?? 1,
                    'unit' => $item['item_unit'] ?? 'pcs',
                    'description' => $item['item_description'] ?? null,
                ]);
            }
        });

        return redirect()->route('shipments.show', $shipment)->with('success', 'Shipment berhasil diperbarui.');
    }

    public function show(Request $request, Shipment $shipment, EtaService $eta)
    {
        abort_if($request->user()->role === 'customer' && $shipment->customer_id !== $request->user()->id, 403);

        $shipment->load(['customer', 'carrier', 'origin', 'destination', 'trackingEvents.location', 'trackingEvents.creator', 'exceptions', 'items']);
        $etaData = $eta->assess($shipment);

        $locations = collect([$shipment->origin, $shipment->destination])
            ->filter()
            ->map(fn ($location) => [
                'name' => $location->name,
                'lat' => (float) $location->latitude,
                'lng' => (float) $location->longitude,
            ])->values();

        $events = $shipment->trackingEvents
            ->filter(fn ($event) => $event->latitude && $event->longitude)
            ->map(fn ($event) => [
                'name' => $event->location?->name ?? $event->event_type,
                'status' => $event->status ?? $event->event_type,
                'time' => $event->event_at?->format('d M Y H:i'),
                'lat' => (float) $event->latitude,
                'lng' => (float) $event->longitude,
            ])->values();

        $mapPoints = $locations->concat($events);

        return view('shipments.show', compact('shipment', 'etaData', 'mapPoints'));
    }

    public function tracking(Request $request, Shipment $shipment, TrackingService $service)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);

        $data = $request->validate([
            'event_type' => 'required|string|max:80',
            'status' => ['nullable', Rule::in(['CREATED', 'BOOKED', 'PICKED_UP', 'IN_TRANSIT', 'DELIVERED'])],
            'event_at' => 'required|date',
            'location_id' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'estimated_arrival_at' => 'nullable|date',
        ]);

        $service->add($shipment, $data, $request->user()->id);

        return back()->with('success', 'Tracking event ditambahkan.');
    }

    public function exception(Request $request, Shipment $shipment, ExceptionService $service)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);

        $data = $request->validate([
            'type' => 'required|in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE',
            'severity' => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'description' => 'required|string',
        ]);

        $service->create($shipment, $data, $request->user()->id);

        return back()->with('success', 'Exception berhasil dicatat dan customer diberi notifikasi.');
    }

    public function exceptions(Request $request)
    {
        $query = ShipmentException::with(['shipment.customer', 'shipment.carrier'])->latest();

        if ($request->user()->role === 'customer') {
            $query->whereHas('shipment', fn ($shipment) => $shipment->where('customer_id', $request->user()->id));
        }

        $exceptions = $query->paginate(15);

        return view('exceptions.index', compact('exceptions'));
    }

    public function updateException(Request $request, ShipmentException $exception, ExceptionService $service)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);

        $data = $request->validate(['status' => 'required|in:OPEN,INVESTIGATING,RESOLVED']);

        $service->update($exception, $data, $request->user()->id);

        return back()->with('success', 'Exception diperbarui dan customer diberi notifikasi.');
    }
}
