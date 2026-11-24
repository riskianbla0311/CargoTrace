<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Shipment;
use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\ShipmentException;
use App\Services\ShipmentService;
use App\Services\ExceptionService;
use App\Services\TrackingService;
use App\Services\EtaService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ShipmentController extends Controller
{
    private function visible(Request $request)
    {
        $query = Shipment::with([
            'customer',
            'carrier',
            'origin',
            'destination'
        ]);

        if ($request->user()->role === 'customer') {
            $query->where('customer_id', $request->user()->id);
        }

        return $query;
    }

    public function index(Request $request)
    {
        $baseQuery = $this->visible($request);

        $shipmentStats = [
            'total' => (clone $baseQuery)->count(),
            'inTransit' => (clone $baseQuery)
                ->where('current_status', 'IN_TRANSIT')->count(),
            'delivered' => (clone $baseQuery)
                ->where('current_status', 'DELIVERED')->count(),
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
                    ->orWhereHas('customer', fn ($customer) =>
                        $customer->where('name', 'like', '%'.$term.'%'))
                    ->orWhereHas('origin', fn ($origin) =>
                        $origin->where('city', 'like', '%'.$term.'%'))
                    ->orWhereHas('destination', fn ($destination) =>
                        $destination->where('city', 'like', '%'.$term.'%'));
            });
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->status);
        }

        if ($request->filled('carrier_id')) {
            $query->where('carrier_id', $request->carrier_id);
        }

        if ($request->filled('origin_id')) {
            $query->where('origin_id', $request->origin_id);
        }

        if ($request->filled('destination_id')) {
            $query->where('destination_id', $request->destination_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->input('delay_status') === 'delayed') {
            $query->whereNotNull('expected_arrival_at')
                ->where('expected_arrival_at', '<', now())
                ->where('current_status', '!=', 'DELIVERED');
        } elseif ($request->input('delay_status') === 'on_time') {
            $query->where(function ($builder) {
                $builder->whereNull('expected_arrival_at')
                    ->orWhere('expected_arrival_at', '>=', now())
                    ->orWhere('current_status', 'DELIVERED');
            });
        }

        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        if (!in_array($sort, [
            'created_at',
            'expected_arrival_at',
            'shipment_number',
            'current_status'
        ], true)) {
            $sort = 'created_at';
        }

        $shipments = $query->orderBy($sort, $direction)
            ->paginate(10)
            ->withQueryString();

        $carriers = Carrier::where('is_active', true)
            ->orderBy('name')->get();

        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('shipments.index', compact(
            'shipments',
            'shipmentStats',
            'carriers',
            'locations'
        ));
    }

    public function create()
    {
        abort_unless(
            in_array(auth()->user()->role, ['admin', 'operator'], true),
            403
        );

        $customers = User::where('role', 'customer')
            ->orderBy('name')->get();

        $carriers = Carrier::where('is_active', true)->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('shipments.create', compact(
            'customers',
            'carriers',
            'locations'
        ));
    }

    public function store(Request $request, ShipmentService $service)
    {
        abort_unless(
            in_array($request->user()->role, ['admin', 'operator'], true),
            403
        );

        $data = $request->validate([
            'shipment_number' => 'required|string|max:60|unique:shipments',
            'shipment_type' => 'required|in:DOMESTIC,INTERNATIONAL',
            'transport_mode' => 'required|in:LAND,SEA,AIR',
            'hs_code' => 'nullable|string|max:30|required_if:shipment_type,INTERNATIONAL',
            'incoterms' => 'nullable|string|max:10',
            'customs_status' => 'nullable|in:NOT_REQUIRED,PENDING,IN_REVIEW,CLEARED,HELD',
            'customs_reference' => 'nullable|string|max:100',
            'customer_id' => 'required|exists:users,id',
            'carrier_id' => ['required', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
            'destination_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
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

        $item = array_intersect_key(
            $data,
            array_flip([
                'item_name',
                'item_quantity',
                'item_unit',
                'item_description',
                'transport_mode'
            ])
        );
        $initialMode = $data['transport_mode'];

        unset(
            $data['item_name'],
            $data['item_quantity'],
            $data['item_unit'],
            $data['item_description'],
            $data['transport_mode']
        );

        $data['current_status'] = 'CREATED';

        $shipment = DB::transaction(function () use (
            $service,
            $data,
            $item,
            $initialMode,
            $request
        ) {
            $created = $service->create(
                $data,
                $request->user()->id
            );

            $created->legs()->create([
                'leg_number' => 1,
                'transport_mode' => $initialMode,
                'carrier_id' => $created->carrier_id,
                'origin_id' => $created->origin_id,
                'destination_id' => $created->destination_id,
                'expected_departure_at' => $created->expected_departure_at,
                'expected_arrival_at' => $created->expected_arrival_at,
                'status' => 'PLANNED',
                'notes' => 'Initial transport leg',
            ]);

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

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', 'Shipment berhasil dibuat.');
    }

    public function edit(Request $request, Shipment $shipment)
    {
        abort_unless(
            in_array($request->user()->role, ['admin', 'operator'], true),
            403
        );

        $customers = User::where('role', 'customer')
            ->orderBy('name')->get();

        $carriers = Carrier::where('is_active', true)
            ->orderBy('name')->get();

        $locations = Location::where('is_active', true)->orderBy('name')->get();

        $shipment->load('items');

        return view('shipments.edit', compact(
            'shipment',
            'customers',
            'carriers',
            'locations'
        ));
    }

    public function update(
        Request $request,
        Shipment $shipment,
        ShipmentService $service
    ) {
        abort_unless(
            in_array($request->user()->role, ['admin', 'operator'], true),
            403
        );

        $data = $request->validate([
            'shipment_number' => [
                'required',
                'string',
                'max:60',
                Rule::unique('shipments', 'shipment_number')
                    ->ignore($shipment->id)
            ],
            'shipment_type' => 'required|in:DOMESTIC,INTERNATIONAL',
            'hs_code' => 'nullable|string|max:30|required_if:shipment_type,INTERNATIONAL',
            'incoterms' => 'nullable|string|max:10',
            'customs_status' => 'nullable|in:NOT_REQUIRED,PENDING,IN_REVIEW,CLEARED,HELD',
            'customs_reference' => 'nullable|string|max:100',
            'customer_id' => 'required|exists:users,id',
            'carrier_id' => ['required', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
            'destination_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
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

        $item = array_intersect_key(
            $data,
            array_flip([
                'item_name',
                'item_quantity',
                'item_unit',
                'item_description'
            ])
        );

        unset(
            $data['item_name'],
            $data['item_quantity'],
            $data['item_unit'],
            $data['item_description']
        );

        DB::transaction(function () use (
            $service,
            $shipment,
            $data,
            $item,
            $request
        ) {
            $service->update(
                $shipment,
                $data,
                $request->user()->id
            );

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

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', 'Shipment berhasil diperbarui.');
    }

    public function show(
        Request $request,
        Shipment $shipment,
        EtaService $eta
    ) {
        abort_if(
            $request->user()->role === 'customer'
            && $shipment->customer_id !== $request->user()->id,
            403
        );

        $shipment->load([
            'customer',
            'carrier',
            'origin',
            'destination',
            'trackingEvents.location',
            'trackingEvents.creator',
            'exceptions',
            'items',
            'legs.carrier',
            'legs.origin',
            'legs.destination',
        ]);

        $etaData = $eta->assess($shipment);

        // Titik lokasi utama shipment.
        $routePoints = collect([
            $shipment->origin,
            $shipment->destination
        ])
            ->filter()
            ->map(fn ($location) => [
                'name' => $location->name,
                'lat' => (float) $location->latitude,
                'lng' => (float) $location->longitude,
            ]);

        // Tambahkan lokasi asal dan tujuan setiap leg ke peta.
        foreach ($shipment->legs as $leg) {
            foreach ([$leg->origin, $leg->destination] as $location) {
                if ($location) {
                    $routePoints->push([
                        'name' => $location->name,
                        'lat' => (float) $location->latitude,
                        'lng' => (float) $location->longitude,
                    ]);
                }
            }
        }

        $events = $shipment->trackingEvents
            ->filter(fn ($event) =>
                $event->latitude !== null && $event->longitude !== null
            )
            ->map(fn ($event) => [
                'name' => $event->location?->name ?? $event->event_type,
                'status' => $event->status ?? $event->event_type,
                'time' => $event->event_at?->format('d M Y H:i'),
                'lat' => (float) $event->latitude,
                'lng' => (float) $event->longitude,
            ]);

        $mapPoints = $routePoints->concat($events)->values();

        // Route segments per transport leg, with mode-specific styling on the detail map.
        $mapLegs = $shipment->legs->map(function ($leg) {
            return [
                'mode' => strtoupper((string) $leg->transport_mode),
                'origin' => $leg->origin?->name ?? $leg->origin?->city ?? 'Origin',
                'destination' => $leg->destination?->name ?? $leg->destination?->city ?? 'Destination',
                'originLat' => $leg->origin?->latitude,
                'originLng' => $leg->origin?->longitude,
                'destinationLat' => $leg->destination?->latitude,
                'destinationLng' => $leg->destination?->longitude,
            ];
        })->filter(function ($leg) {
            return is_numeric($leg['originLat']) && is_numeric($leg['originLng'])
                && is_numeric($leg['destinationLat']) && is_numeric($leg['destinationLng']);
        })->values();

        // Data untuk dropdown form shipment leg.
        $carriers = Carrier::where('is_active', true)
            ->orderBy('name')
            ->get();

        $locations = Location::where('is_active', true)->orderBy('country')
            ->orderBy('city')
            ->orderBy('name')
            ->get();

        $canManage = in_array(
            $request->user()->role,
            ['admin', 'operator'],
            true
        );

        return view('shipments.show', compact(
            'shipment',
            'etaData',
            'mapPoints',
            'mapLegs',
            'carriers',
            'locations',
            'canManage'
        ));
    }

    /** Return the latest persisted tracking data for lightweight customer live refresh. */
    public function liveTracking(Request $request, Shipment $shipment, EtaService $eta)
    {
        abort_if(
            $request->user()->role === 'customer'
            && $shipment->customer_id !== $request->user()->id,
            403
        );

        $events = $shipment->trackingEvents()
            ->with(['location', 'creator'])
            ->latest('event_at')
            ->limit(30)
            ->get()
            ->map(fn ($event) => [
                'id' => $event->id,
                'event_type' => $event->event_type,
                'status' => $event->status,
                'event_at' => $event->event_at?->toIso8601String(),
                'event_at_label' => $event->event_at?->format('d M Y H:i'),
                'location' => $event->location?->name ?? 'Location not specified',
                'description' => $event->description,
                'creator' => $event->creator?->name ?? 'System',
                'latitude' => $event->latitude,
                'longitude' => $event->longitude,
            ])->values();

        return response()->json([
            'shipment_status' => $shipment->fresh()->current_status,
            'eta' => $eta->assess($shipment),
            'events' => $events,
            'checked_at' => now()->toIso8601String(),
        ]);
    }

    public function tracking(
        Request $request,
        Shipment $shipment,
        TrackingService $service
    ) {
        abort_unless(
            in_array($request->user()->role, ['admin', 'operator'], true),
            403
        );

        $data = $request->validate([
            'event_type' => 'required|string|max:80',
            'status' => [
                'nullable',
                Rule::in([
                    'CREATED',
                    'BOOKED',
                    'PICKED_UP',
                    'IN_TRANSIT',
                    'DELIVERED'
                ])
            ],
            'event_at' => 'required|date',
            'location_id' => 'nullable|exists:locations,id',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'description' => 'nullable|string',
            'estimated_arrival_at' => 'nullable|date',
        ]);

        $service->add(
            $shipment,
            $data,
            $request->user()->id
        );

        return back()->with(
            'success',
            'Tracking event ditambahkan.'
        );
    }

    /**
     * Menambahkan tahap perjalanan multimodal.
     */
    public function storeLeg(Request $request, Shipment $shipment)
    {
        abort_unless(
            in_array($request->user()->role, ['admin', 'operator'], true),
            403
        );

        $data = $request->validate([
            'transport_mode' => [
                'required',
                Rule::in(['LAND', 'SEA', 'AIR'])
            ],
            'carrier_id' => ['nullable', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
            'destination_id' => [
                'required',
                'different:origin_id',
                Rule::exists('locations', 'id')->where('is_active', true),
            ],
            'expected_departure_at' => 'nullable|date',
            'expected_arrival_at' => [
                'nullable',
                'date',
                function ($attribute, $value, $fail) use ($request) {
                    $departure = $request->input('expected_departure_at');

                    if (
                        $value &&
                        $departure &&
                        strtotime($value) < strtotime($departure)
                    ) {
                        $fail('Jadwal kedatangan tidak boleh sebelum keberangkatan.');
                    }
                }
            ],
            'notes' => 'nullable|string|max:2000',
        ]);

        $userId = $request->user()->id;

        DB::transaction(function () use ($shipment, $data, $userId) {
            $lockedShipment = Shipment::whereKey($shipment->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lastLegNumber = $lockedShipment->legs()
                ->max('leg_number') ?? 0;

            $leg = $lockedShipment->legs()->create([
                'leg_number' => $lastLegNumber + 1,
                'transport_mode' => $data['transport_mode'],
                'carrier_id' => $data['carrier_id'] ?? null,
                'origin_id' => $data['origin_id'],
                'destination_id' => $data['destination_id'],
                'expected_departure_at' => $data['expected_departure_at'] ?? null,
                'expected_arrival_at' => $data['expected_arrival_at'] ?? null,
                'status' => 'PLANNED',
                'notes' => $data['notes'] ?? null,
            ]);

            \App\Models\AuditLog::create([
                'user_id' => $userId,
                'action' => 'shipment.leg_created',
                'entity_type' => \App\Models\ShipmentLeg::class,
                'entity_id' => $leg->id,
                'details' => ['shipment_id' => $lockedShipment->id, 'leg_number' => $leg->leg_number, 'transport_mode' => $leg->transport_mode],
                'created_at' => now(),
            ]);

            \App\Models\Notification::create([
                'user_id' => $lockedShipment->customer_id,
                'title' => 'Shipment route updated',
                'message' => 'A new ' . $leg->transport_mode . ' transport stage was added to ' . $lockedShipment->shipment_number . '.',
                'type' => 'tracking',
                'data' => ['shipment_id' => $lockedShipment->id, 'leg_id' => $leg->id],
            ]);
        });

        return redirect()
            ->route('shipments.show', $shipment)
            ->with('success', 'Tahap perjalanan berhasil ditambahkan.');
    }

    /** Update a transport leg belonging to the selected shipment. */
    public function updateLeg(Request $request, Shipment $shipment, \App\Models\ShipmentLeg $leg)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);
        abort_unless((int) $leg->shipment_id === (int) $shipment->id, 404);

        $data = $request->validate([
            'transport_mode' => ['required', Rule::in(['LAND', 'SEA', 'AIR'])],
            'carrier_id' => ['nullable', Rule::exists('carriers', 'id')->where('is_active', true)],
            'origin_id' => ['required', Rule::exists('locations', 'id')->where('is_active', true)],
            'destination_id' => ['required', 'different:origin_id', Rule::exists('locations', 'id')->where('is_active', true)],
            'expected_departure_at' => ['nullable', 'date'],
            'expected_arrival_at' => ['nullable', 'date', 'after_or_equal:expected_departure_at'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($leg, $data, $request, $shipment) {
            $leg->update($data);

            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'shipment.leg_updated',
                'entity_type' => \App\Models\ShipmentLeg::class,
                'entity_id' => $leg->id,
                'details' => ['shipment_id' => $shipment->id, 'leg_number' => $leg->leg_number, 'transport_mode' => $leg->transport_mode],
                'created_at' => now(),
            ]);

            \App\Models\Notification::create([
                'user_id' => $shipment->customer_id,
                'title' => 'Shipment route updated',
                'message' => 'A transport stage was updated for ' . $shipment->shipment_number . '.',
                'type' => 'tracking',
                'data' => ['shipment_id' => $shipment->id, 'leg_id' => $leg->id],
            ]);
        });

        return redirect()->route('shipments.show', $shipment)->with('success', 'Transport leg berhasil diperbarui.');
    }

    /** Delete a transport leg belonging to the selected shipment. */
    public function destroyLeg(Request $request, Shipment $shipment, \App\Models\ShipmentLeg $leg)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);
        abort_unless((int) $leg->shipment_id === (int) $shipment->id, 404);

        DB::transaction(function () use ($leg, $request, $shipment) {
            $details = ['shipment_id' => $shipment->id, 'leg_number' => $leg->leg_number, 'transport_mode' => $leg->transport_mode];
            \App\Models\AuditLog::create([
                'user_id' => $request->user()->id,
                'action' => 'shipment.leg_deleted',
                'entity_type' => \App\Models\ShipmentLeg::class,
                'entity_id' => $leg->id,
                'details' => $details,
                'created_at' => now(),
            ]);
            $leg->delete();
            \App\Models\Notification::create([
                'user_id' => $shipment->customer_id,
                'title' => 'Shipment route updated',
                'message' => 'A transport stage was removed from ' . $shipment->shipment_number . '.',
                'type' => 'tracking',
                'data' => ['shipment_id' => $shipment->id, 'leg_id' => $leg->id],
            ]);
        });

        return redirect()->route('shipments.show', $shipment)->with('success', 'Transport leg berhasil dihapus.');
    }

    public function exception(
        Request $request,
        Shipment $shipment,
        ExceptionService $service
    ) {
        abort_unless(
            in_array($request->user()->role, ['admin', 'operator'], true),
            403
        );

        $data = $request->validate([
            'type' => 'required|in:CUSTOMS_HOLD,WEATHER_DELAY,PORT_CONGESTION,DAMAGED,MISROUTED,DOCUMENTATION_ISSUE',
            'severity' => 'required|in:LOW,MEDIUM,HIGH,CRITICAL',
            'description' => 'required|string',
        ]);

        $service->create(
            $shipment,
            $data,
            $request->user()->id
        );

        return back()->with(
            'success',
            'Exception berhasil dicatat dan customer diberi notifikasi.'
        );
    }


    public function archive(Request $request, Shipment $shipment)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);

        $shipment->delete(); // SoftDeletes: data tetap tersimpan di database.

        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'shipment.archived',
            'entity_type' => Shipment::class,
            'entity_id' => $shipment->id,
            'details' => ['shipment_number' => $shipment->shipment_number],
            'created_at' => now(),
        ]);

        return redirect()->route('shipments.index')->with('success', 'Shipment berhasil diarsipkan. Data tidak dihapus permanen.');
    }

    /**
     * Show archived shipments to operational staff. Customers never access this page.
     */
    public function archived(Request $request)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);

        $query = Shipment::onlyTrashed()->with([
            'customer', 'carrier', 'origin', 'destination'
        ]);

        if ($request->filled('q')) {
            $term = $request->q;
            $query->where(function ($builder) use ($term) {
                $builder->where('shipment_number', 'like', '%'.$term.'%')
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', '%'.$term.'%'));
            });
        }

        $shipments = $query->orderByDesc('deleted_at')->paginate(10)->withQueryString();

        return view('shipments.archived', compact('shipments'));
    }

    /** Restore is restricted to administrators. */
    public function restore(Request $request, Shipment $shipment)
    {
        abort_unless($request->user()->role === 'admin', 403);
        abort_unless($shipment->trashed(), 404);

        $shipment->restore();

        \App\Models\AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'shipment.restored',
            'entity_type' => Shipment::class,
            'entity_id' => $shipment->id,
            'details' => ['shipment_number' => $shipment->shipment_number],
            'created_at' => now(),
        ]);

        return redirect()->route('shipments.archived')->with('success', 'Shipment berhasil dipulihkan ke daftar aktif.');
    }

    public function exceptions(Request $request)
    {
        $query = ShipmentException::with([
            'shipment.customer',
            'shipment.carrier'
        ])->latest();

        if ($request->user()->role === 'customer') {
            $query->whereHas(
                'shipment',
                fn ($shipment) =>
                    $shipment->where(
                        'customer_id',
                        $request->user()->id
                    )
            );
        }

        $exceptions = $query->paginate(15);

        return view('exceptions.index', compact('exceptions'));
    }

    public function updateException(
        Request $request,
        ShipmentException $exception,
        ExceptionService $service
    ) {
        abort_unless(
            in_array($request->user()->role, ['admin', 'operator'], true),
            403
        );

        $data = $request->validate([
            'status' => 'required|in:OPEN,INVESTIGATING,RESOLVED'
        ]);

        $service->update(
            $exception,
            $data,
            $request->user()->id
        );

        return back()->with(
            'success',
            'Exception diperbarui dan customer diberi notifikasi.'
        );
    }
}
