<?php

namespace App\Http\Controllers;

use App\Models\TrackingEvent;
use App\Models\Shipment;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless(
            in_array($user->role, ['admin', 'operator', 'customer'], true),
            403
        );

        $baseQuery = TrackingEvent::with([
            'shipment.customer',
            'shipment.carrier',
            'shipment.origin',
            'shipment.destination',
            'location',
            'creator',
        ]);

        if ($user->role === 'customer') {
            $baseQuery->whereHas('shipment', function ($shipment) use ($user) {
                $shipment->where('customer_id', $user->id);
            });
        }

        $totalEvents = (clone $baseQuery)->count();

        $inTransit = (clone $baseQuery)
            ->where('status', 'IN_TRANSIT')
            ->count();

        $delivered = (clone $baseQuery)
            ->where('status', 'DELIVERED')
            ->count();

        $delayed = Shipment::query()
            ->when($user->role === 'customer', function ($query) use ($user) {
                $query->where('customer_id', $user->id);
            })
            ->whereNotNull('expected_arrival_at')
            ->where('expected_arrival_at', '<', now())
            ->where('current_status', '!=', 'DELIVERED')
            ->count();

        $query = clone $baseQuery;

        if ($request->filled('q')) {
            $term = $request->input('q');

            $query->where(function ($builder) use ($term) {
                $builder->where('event_type', 'like', '%' . $term . '%')
                    ->orWhereHas('shipment', function ($shipment) use ($term) {
                        $shipment->where('shipment_number', 'like', '%' . $term . '%');
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $trackingEvents = $query
            ->orderByDesc('event_at')
            ->paginate(15)
            ->withQueryString();

        // Ambil detail shipment yang dipilih.
        $selectedShipment = null;

        if ($request->filled('shipment_id')) {
            $shipmentQuery = Shipment::with([
                'customer',
                'carrier',
                'origin',
                'destination',
                'trackingEvents.location',
                'trackingEvents.creator',
                'exceptions',
            ]);

            if ($user->role === 'customer') {
                $shipmentQuery->where('customer_id', $user->id);
            }

            $selectedShipment = $shipmentQuery->findOrFail(
                $request->input('shipment_id')
            );
        }

        return view('tracking.index', compact(
            'trackingEvents',
            'totalEvents',
            'inTransit',
            'delivered',
            'delayed',
            'selectedShipment'
        ));
    }
}