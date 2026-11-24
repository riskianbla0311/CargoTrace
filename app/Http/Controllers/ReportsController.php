<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\Carrier;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        abort_unless(
            in_array($request->user()->role, ['admin', 'operator'], true),
            403
        );

        $query = Shipment::with(['carrier', 'customer']);

        // Filter tanggal pembuatan shipment.
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $shipments = $query->get();

        // Statistik shipment.
        $total = $shipments->count();

        $created = $shipments->where('current_status', 'CREATED')->count();
        $booked = $shipments->where('current_status', 'BOOKED')->count();
        $pickedUp = $shipments->where('current_status', 'PICKED_UP')->count();
        $inTransit = $shipments->where('current_status', 'IN_TRANSIT')->count();
        $delivered = $shipments->where('current_status', 'DELIVERED')->count();

        $delayed = $shipments->filter(function ($shipment) {
            return $shipment->expected_arrival_at
                && $shipment->expected_arrival_at->isPast()
                && $shipment->current_status !== 'DELIVERED';
        })->count();

        $openExceptions = ShipmentException::whereIn(
            'status',
            ['OPEN', 'INVESTIGATING']
        )->whereHas('shipment', function ($builder) use ($shipments) {
            $builder->whereIn('id', $shipments->pluck('id'));
        })->count();

        // Rekap berdasarkan status.
        $statusStats = [
            'CREATED' => $created,
            'BOOKED' => $booked,
            'PICKED_UP' => $pickedUp,
            'IN_TRANSIT' => $inTransit,
            'DELIVERED' => $delivered,
        ];

        // Rekap berdasarkan carrier.
        $carrierStats = $shipments
            ->groupBy(function ($shipment) {
                return $shipment->carrier?->name ?? 'Unknown Carrier';
            })
            ->map(function ($items, $carrierName) {
                return [
                    'name' => $carrierName,
                    'total' => $items->count(),
                    'delivered' => $items->where('current_status', 'DELIVERED')->count(),
                    'in_transit' => $items->where('current_status', 'IN_TRANSIT')->count(),
                ];
            })
            ->values();

        $deliveryRate = $total > 0
            ? round(($delivered / $total) * 100, 1)
            : 0;

        $delayRate = $total > 0
            ? round(($delayed / $total) * 100, 1)
            : 0;

        return view('reports.index', compact(
            'shipments',
            'total',
            'created',
            'booked',
            'pickedUp',
            'inTransit',
            'delivered',
            'delayed',
            'openExceptions',
            'statusStats',
            'carrierStats',
            'deliveryRate',
            'delayRate'
        ));
    }
}