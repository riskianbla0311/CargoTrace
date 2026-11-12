<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeocodingService;
use Illuminate\Http\Request;

class GeocodingController extends Controller
{
    public function search(Request $request, GeocodingService $geocoding)
    {
        abort_unless(in_array($request->user()->role, ['admin', 'operator'], true), 403);

        $data = $request->validate(['q' => 'required|string|min:3|max:180']);
        return response()->json($geocoding->search($data['q']));
    }
}
