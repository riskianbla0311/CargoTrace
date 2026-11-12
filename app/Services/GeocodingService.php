<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    public function search(string $query): array
    {
        try {
            $response = Http::acceptJson()
                ->withHeaders(['User-Agent' => 'CargoTraceAcademicProject/1.0'])
                ->timeout(8)
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'jsonv2',
                    'limit' => 5,
                    'addressdetails' => 1,
                ]);

            if (!$response->successful()) {
                return ['available' => false, 'message' => 'Layanan geocoding sedang tidak tersedia.', 'results' => []];
            }

            $results = collect($response->json() ?? [])->map(fn ($place) => [
                'display_name' => $place['display_name'] ?? '',
                'latitude' => isset($place['lat']) ? (float) $place['lat'] : null,
                'longitude' => isset($place['lon']) ? (float) $place['lon'] : null,
                'type' => $place['type'] ?? null,
            ])->values()->all();

            return ['available' => true, 'results' => $results];
        } catch (\Throwable $exception) {
            return ['available' => false, 'message' => 'Tidak dapat terhubung ke layanan geocoding.', 'results' => []];
        }
    }
}
