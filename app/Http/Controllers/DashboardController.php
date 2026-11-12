<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Shipment;
use App\Models\ShipmentException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = $user->role;

        // Customer hanya melihat shipment miliknya sendiri.
        $query = Shipment::query();

        if ($role === 'customer') {
            $query->where('customer_id', $user->id);
        }

        $total = (clone $query)->count();
        $inTransit = (clone $query)->where('current_status', 'IN_TRANSIT')->count();
        $delivered = (clone $query)->where('current_status', 'DELIVERED')->count();

        $delayedQuery = (clone $query)
            ->whereNotNull('expected_arrival_at')
            ->where('expected_arrival_at', '<', now())
            ->where('current_status', '!=', 'DELIVERED');

        $delayed = (clone $delayedQuery)->count();

        $allExceptions = ShipmentException::whereHas('shipment', function ($shipmentQuery) use ($user, $role) {
            if ($role === 'customer') $shipmentQuery->where('customer_id', $user->id);
        })->count();
        $exceptions = ShipmentException::where('status', '!=', 'RESOLVED')
            ->whereHas('shipment', function ($shipmentQuery) use ($user, $role) {
                if ($role === 'customer') {
                    $shipmentQuery->where('customer_id', $user->id);
                }
            })
            ->count();

        $exceptionRate = $total > 0 ? round(($allExceptions / $total) * 100, 1) : 0;
        $shipmentByCarrier = (clone $query)
            ->with('carrier')
            ->whereNotNull('carrier_id')
            ->get()
            ->groupBy(fn ($shipment) => $shipment->carrier?->name ?? 'Unknown')
            ->map(fn ($items) => $items->count());

        $statuses = (clone $query)
            ->select('current_status', DB::raw('COUNT(*) as total'))
            ->groupBy('current_status')
            ->pluck('total', 'current_status');

        $recent = (clone $query)
            ->with(['customer', 'carrier', 'origin', 'destination'])
            ->latest()
            ->take(10)
            ->get();

        // Titik peta berasal dari koordinat lokasi asal dan tujuan shipment.
        $mapShipments = (clone $query)
            ->with(['origin', 'destination'])
            ->latest()
            ->take(100)
            ->get();

        $mapPoints = $mapShipments->flatMap(function ($shipment) {
            return collect([
                [
                    'shipment' => $shipment->shipment_number,
                    'place' => $shipment->origin?->name ?? $shipment->origin?->city ?? 'Origin',
                    'lat' => $shipment->origin?->latitude,
                    'lng' => $shipment->origin?->longitude,
                    'type' => 'Origin',
                ],
                [
                    'shipment' => $shipment->shipment_number,
                    'place' => $shipment->destination?->name ?? $shipment->destination?->city ?? 'Destination',
                    'lat' => $shipment->destination?->latitude,
                    'lng' => $shipment->destination?->longitude,
                    'type' => 'Destination',
                ],
            ]);
        })->filter(function ($point) {
            return is_numeric($point['lat']) && is_numeric($point['lng']);
        })->values();

        // Statistik keterlambatan per carrier untuk grafik dashboard.
        $carrierShipments = (clone $query)
            ->with('carrier')
            ->whereNotNull('carrier_id')
            ->whereNotNull('expected_arrival_at')
            ->get(['id', 'carrier_id', 'expected_arrival_at', 'current_status']);

        $carrierDelayStats = $carrierShipments
            ->groupBy(function ($shipment) {
                return $shipment->carrier?->name ?? 'Unknown carrier';
            })
            ->map(function ($items) {
                $late = $items->filter(function ($shipment) {
                    return $shipment->expected_arrival_at
                        && $shipment->expected_arrival_at->isPast()
                        && $shipment->current_status !== 'DELIVERED';
                })->count();

                return [
                    'total' => $items->count(),
                    'delayed' => $late,
                    'rate' => $items->count() ? round(($late / $items->count()) * 100, 1) : 0,
                ];
            });

        $etaShipments = (clone $query)
            ->with(['carrier', 'origin', 'destination'])
            ->whereNotNull('expected_arrival_at')
            ->where('current_status', '!=', 'DELIVERED')
            ->orderBy('expected_arrival_at')
            ->take(6)
            ->get();

        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'role',
            'total',
            'inTransit',
            'delivered',
            'delayed',
            'exceptions',
            'exceptionRate',
            'shipmentByCarrier',
            'statuses',
            'recent',
            'mapPoints',
            'carrierDelayStats',
            'etaShipments',
            'notifications'
        ));
    }

    public function notifications(Request $request)
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function readNotification(Request $request, Notification $notification)
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notification marked as read.');
    }
}
