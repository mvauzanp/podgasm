<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\BiteshipService;

class ShippingController extends Controller
{
    /**
     * AJAX endpoint untuk mencari kecamatan/kota tujuan (Biteship Maps API)
     */
    public function searchShippingAreas(Request $request)
    {
        $query = $request->query('q', '');
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $biteship = app(BiteshipService::class);
        $areas = $biteship->searchAreas($query);

        return response()->json($areas);
    }

    /**
     * AJAX endpoint untuk pencarian tempat/jalan spesifik (OpenStreetMap + Biteship Forward Geocoding)
     */
    public function searchPlaces(Request $request)
    {
        $query = trim($request->query('q', ''));
        if (strlen($query) < 3) {
            return response()->json([]);
        }

        $cacheKey = 'place_search_' . md5(strtolower($query));

        $results = Cache::remember($cacheKey, 3600, function () use ($query) {
            $places = [];

            try {
                // 1. Cari via OpenStreetMap Nominatim
                $response = Http::withHeaders([
                    'User-Agent' => 'PodgasmEcommerce/1.0 (admin@podgasm.com)',
                    'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8'
                ])->timeout(5)->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'countrycodes' => 'id',
                    'format' => 'jsonv2',
                    'addressdetails' => 1,
                    'limit' => 6
                ]);

                if ($response->successful()) {
                    $biteship = app(BiteshipService::class);
                    foreach ($response->json() as $item) {
                        $addr = $item['address'] ?? [];
                        $district = $addr['municipality'] ?? ($addr['city_district'] ?? ($addr['suburb'] ?? ($addr['village'] ?? '')));
                        $city = $addr['city'] ?? ($addr['county'] ?? ($addr['state_district'] ?? ''));
                        $road = $addr['road'] ?? ($addr['amenity'] ?? ($addr['neighbourhood'] ?? ''));

                        // Coba cari matching area di Biteship
                        $biteshipArea = null;
                        if ($district || $city) {
                            $searchQuery = trim($district . ' ' . $city);
                            $matchedAreas = $biteship->searchAreas($searchQuery);
                            if (!empty($matchedAreas)) {
                                $biteshipArea = $matchedAreas[0];
                            }
                        }

                        $places[] = [
                            'display_name' => $item['display_name'],
                            'street_name' => $road,
                            'district' => $district,
                            'city' => $city,
                            'postal_code' => $addr['postcode'] ?? null,
                            'latitude' => (float) $item['lat'],
                            'longitude' => (float) $item['lon'],
                            'biteship_area_id' => $biteshipArea['id'] ?? null,
                            'biteship_area_name' => $biteshipArea['name'] ?? null,
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('searchPlaces OpenStreetMap exception: ' . $e->getMessage());
            }

            // 2. Jika Nominatim kosong, fallback ke Biteship Areas
            if (empty($places)) {
                $biteship = app(BiteshipService::class);
                $biteshipAreas = $biteship->searchAreas($query);
                foreach ($biteshipAreas as $area) {
                    $places[] = [
                        'display_name' => $area['name'] . ($area['postal_code'] ? ' (' . $area['postal_code'] . ')' : ''),
                        'street_name' => '',
                        'district' => $area['district'] ?? '',
                        'city' => $area['city'] ?? '',
                        'postal_code' => $area['postal_code'] ?? null,
                        'latitude' => null,
                        'longitude' => null,
                        'biteship_area_id' => $area['id'],
                        'biteship_area_name' => $area['name'],
                    ];
                }
            }

            return $places;
        });

        return response()->json($results);
    }

    /**
     * AJAX endpoint Reverse Geocoding (Titik Peta GPS -> Teks Alamat & Kecamatan Biteship)
     */
    public function reverseGeocode(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $lat = round((float) $request->lat, 5);
        $lng = round((float) $request->lng, 5);

        $cacheKey = "rev_geo_{$lat}_{$lng}";

        $result = Cache::remember($cacheKey, 86400, function () use ($lat, $lng) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'PodgasmEcommerce/1.0 (admin@podgasm.com)',
                    'Accept-Language' => 'id-ID,id;q=0.9,en;q=0.8'
                ])->timeout(6)->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'jsonv2',
                    'addressdetails' => 1
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $addr = $data['address'] ?? [];

                    $road = $addr['road'] ?? ($addr['amenity'] ?? ($addr['building'] ?? ''));
                    $houseNumber = $addr['house_number'] ?? '';
                    $neighbourhood = $addr['neighbourhood'] ?? ($addr['hamlet'] ?? '');
                    $village = $addr['village'] ?? ($addr['suburb'] ?? '');
                    $district = $addr['municipality'] ?? ($addr['city_district'] ?? ($addr['district'] ?? ''));
                    $city = $addr['city'] ?? ($addr['county'] ?? ($addr['town'] ?? ''));
                    $state = $addr['state'] ?? '';
                    $postcode = $addr['postcode'] ?? '';

                    // Susun format jalan & nomor rumah
                    $streetParts = array_filter([
                        $road ? ($road . ($houseNumber ? ' No. ' . $houseNumber : '')) : '',
                        $neighbourhood,
                        $village ? 'Kel. ' . $village : ''
                    ]);
                    $streetAddress = implode(', ', $streetParts);

                    if (empty($streetAddress) && !empty($data['display_name'])) {
                        // Ambil 2 bagian pertama dari display name
                        $parts = explode(',', $data['display_name']);
                        $streetAddress = trim($parts[0] ?? '') . (isset($parts[1]) ? ', ' . trim($parts[1]) : '');
                    }

                    // Cari matching area id di Biteship
                    $biteship = app(BiteshipService::class);
                    $biteshipArea = null;

                    $searchQuery = trim(($district ?: $village) . ' ' . $city);
                    if ($searchQuery) {
                        $matchedAreas = $biteship->searchAreas($searchQuery);
                        if (!empty($matchedAreas)) {
                            $biteshipArea = $matchedAreas[0];
                        }
                    }

                    if (!$biteshipArea && $city) {
                        $matchedAreas = $biteship->searchAreas($city);
                        if (!empty($matchedAreas)) {
                            $biteshipArea = $matchedAreas[0];
                        }
                    }

                    return [
                        'success' => true,
                        'street_address' => $streetAddress ?: 'Jl. Lokasi Terpilih',
                        'full_display' => $data['display_name'] ?? '',
                        'village' => $village,
                        'district' => $district,
                        'city' => $city,
                        'state' => $state,
                        'postal_code' => $postcode,
                        'latitude' => $lat,
                        'longitude' => $lng,
                        'biteship_area_id' => $biteshipArea['id'] ?? null,
                        'biteship_area_name' => $biteshipArea['name'] ?? trim(($district ? $district . ', ' : '') . $city . ($state ? ', ' . $state : '')),
                    ];
                }
            } catch (\Exception $e) {
                Log::error('reverseGeocode exception: ' . $e->getMessage());
            }

            return [
                'success' => false,
                'message' => 'Gagal mendeteksi detail alamat dari koordinat.'
            ];
        });

        return response()->json($result);
    }

    /**
     * AJAX endpoint untuk mendapatkan opsi kurir dan ongkir
     */
    public function getShippingRates(Request $request)
    {
        $request->validate([
            'destination_area_id' => 'required|string|max:100'
        ]);

        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $cart = Cart::getOrCreateForUser(Auth::id());
        $items = $cart->items()->with(['product', 'variant'])->get();

        if ($items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Keranjang kosong.'], 422);
        }

        $biteship = app(BiteshipService::class);
        $biteshipItems = $biteship->formatCartItems($items);

        $rates = $biteship->getRates(
            $request->destination_area_id, 
            $biteshipItems, 
            $request->filled('latitude') ? (float) $request->latitude : null,
            $request->filled('longitude') ? (float) $request->longitude : null
        );

        return response()->json($rates);
    }
}
