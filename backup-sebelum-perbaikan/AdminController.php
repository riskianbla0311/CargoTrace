<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Carrier;
use App\Models\Location;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function users()
    {
        $users = User::latest()->paginate(20);
        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,operator,customer',
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'user.created', 'entity_type' => User::class, 'entity_id' => $user->id, 'details' => ['email' => $user->email, 'role' => $user->role], 'created_at' => now()]);
        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:admin,operator,customer',
        ]);
        $user->update($data);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'user.updated', 'entity_type' => User::class, 'entity_id' => $user->id, 'details' => $data, 'created_at' => now()]);
        return back()->with('success', 'Data user diperbarui.');
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => 'required|in:admin,operator,customer']);
        $user->update($data);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'user.role_updated',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'details' => $data,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Role user diperbarui.');
    }

    public function carriers()
    {
        $carrierQuery = Carrier::withCount([
            'shipments',
            'shipments as delivered_shipments_count' => function ($query) {
                $query->where('current_status', 'DELIVERED');
            },
        ]);

        if (request()->filled('q')) {
            $term = request('q');
            $carrierQuery->where(function ($query) use ($term) {
                $query->where('name', 'like', '%'.$term.'%')
                    ->orWhere('code', 'like', '%'.$term.'%');
            });
        }

        $carriers = $carrierQuery->orderBy('name')->paginate(12)->withQueryString();

        $carrierStats = [
            'total' => Carrier::count(),
            'active' => Carrier::where('is_active', true)->count(),
            'shipments' => \App\Models\Shipment::count(),
            'successRate' => $this->carrierSuccessRate(),
        ];

        return view('admin.carriers', compact('carriers', 'carrierStats'));
    }

    private function carrierSuccessRate(): float
    {
        $total = \App\Models\Shipment::count();

        if ($total === 0) {
            return 0;
        }

        $delivered = \App\Models\Shipment::where('current_status', 'DELIVERED')->count();

        return round(($delivered / $total) * 100, 1);
    }

    public function updateCarrier(Request $request, Carrier $carrier)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'required|string|max:30|unique:carriers,code,'.$carrier->id,
            'website' => 'nullable|url',
            'contact_email' => 'nullable|email',
            'phone' => 'nullable|string|max:40',
            'is_active' => 'required|boolean',
        ]);
        $carrier->update($data);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'carrier.updated', 'entity_type' => Carrier::class, 'entity_id' => $carrier->id, 'details' => $data, 'created_at' => now()]);
        return back()->with('success', 'Carrier diperbarui.');
    }

    public function storeCarrier(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'required|string|max:30|unique:carriers',
            'website' => 'nullable|url',
            'contact_email' => 'nullable|email',
            'phone' => 'nullable|string|max:40',
        ]);

        $carrier = Carrier::create($data + ['is_active' => true]);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'carrier.created', 'entity_type' => Carrier::class, 'entity_id' => $carrier->id, 'details' => ['name' => $carrier->name], 'created_at' => now()]);
        return back()->with('success', 'Carrier ditambahkan.');
    }

    public function locations()
    {
        $locations = Location::latest()->paginate(20);
        return view('admin.locations', compact('locations'));
    }

    public function updateLocation(Request $request, Location $location)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_type' => 'nullable|string|max:50',
        ]);
        $location->update($data);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'location.updated', 'entity_type' => Location::class, 'entity_id' => $location->id, 'details' => $data, 'created_at' => now()]);
        return back()->with('success', 'Lokasi diperbarui.');
    }

    public function storeLocation(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'city' => 'required|string|max:100',
            'country' => 'required|string|max:100',
            'address' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'location_type' => 'nullable|string|max:50',
        ]);

        $location = Location::create($data);
        AuditLog::create(['user_id' => $request->user()->id, 'action' => 'location.created', 'entity_type' => Location::class, 'entity_id' => $location->id, 'details' => ['name' => $location->name], 'created_at' => now()]);
        return back()->with('success', 'Lokasi ditambahkan.');
    }

    public function audit()
    {
        $logs = AuditLog::with('user')->latest('created_at')->paginate(30);
        return view('admin.audit', compact('logs'));
    }
}
