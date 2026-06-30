<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BiteshipService
{
    protected $apiKey;
    protected $baseUrl = 'https://api.biteship.com';
    protected $originAreaId;
    protected $originAddress;
    protected $originPostalCode;
    protected $originPhone;
    protected $originName;

    public function __construct()
    {
        $this->apiKey = config('services.biteship.api_key');
        $this->originAreaId = config('services.biteship.origin_area_id', 'IDNP3CL10');
        $this->originAddress = config('services.biteship.origin_address', 'Gudang Pusat Podgasm, Jakarta Selatan');
        $this->originPostalCode = config('services.biteship.origin_postal_code', '12190');
        $this->originPhone = config('services.biteship.origin_phone', '08123456789');
        $this->originName = config('services.biteship.origin_name', 'Podgasm Warehouse Admin');
    }

    /**
     * Helper untuk HTTP Client Biteship
     */
    protected function client()
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(10);
    }

    /**
     * Cek apakah API Key sudah dikonfigurasi
     */
    public function hasApiKey()
    {
        return !empty($this->apiKey) && $this->apiKey !== 'xxx' && $this->apiKey !== 'YOUR_BITESHIP_API_KEY';
    }

    /**
     * Pencarian Area/Wilayah Autocomplete
     * 
     * @param string $query Nama kecamatan/kota
     * @return array
     */
    public function searchAreas(string $query)
    {
        if (!$this->hasApiKey()) {
            return $this->getMockAreas($query);
        }

        try {
            $response = $this->client()->get($this->baseUrl . '/v1/maps/areas', [
                'countries' => 'ID',
                'input' => $query,
                'type' => 'single'
            ]);

            if ($response->successful()) {
                return $response->json()['areas'] ?? [];
            }

            Log::error('Biteship searchAreas failed: ' . $response->body());
            return $this->getMockAreas($query);
        } catch (\Exception $e) {
            Log::error('Biteship searchAreas exception: ' . $e->getMessage());
            return $this->getMockAreas($query);
        }
    }

    /**
     * Hitung Tarif Ongkos Kirim Real-Time
     * 
     * @param string $destinationAreaId Area ID tujuan
     * @param array $items List item belanja
     * @return array
     */
    public function getRates(string $destinationAreaId, array $items)
    {
        // Siapkan format item untuk Biteship API
        $biteshipItems = [];
        $totalWeight = 0;

        foreach ($items as $item) {
            // Jika product/variant memiliki berat gunakan itu, jika tidak default 200 gram
            $weight = $item['weight'] ?? 200; // Gram
            $totalWeight += ($weight * $item['quantity']);

            $biteshipItems[] = [
                'name' => $item['name'],
                'value' => (int) $item['price'],
                'weight' => (int) $weight,
                'quantity' => (int) $item['quantity']
            ];
        }

        if (!$this->hasApiKey()) {
            return $this->getMockRates($destinationAreaId, $totalWeight);
        }

        try {
            $payload = [
                'origin_area_id' => $this->originAreaId,
                'destination_area_id' => $destinationAreaId,
                'couriers' => 'jne,jnt,sicepat,tiki,anteraja',
                'items' => $biteshipItems
            ];

            $response = $this->client()->post($this->baseUrl . '/v1/rates/couriers', $payload);

            if ($response->successful()) {
                return $response->json()['pricing'] ?? [];
            }

            Log::error('Biteship getRates failed: ' . $response->body());
            return $this->getMockRates($destinationAreaId, $totalWeight);
        } catch (\Exception $e) {
            Log::error('Biteship getRates exception: ' . $e->getMessage());
            return $this->getMockRates($destinationAreaId, $totalWeight);
        }
    }

    /**
     * Membuat Pesanan Pengiriman ke Biteship (Booking Kurir & Request Pickup)
     * 
     * @param array $orderData
     * @return array
     */
    public function createOrder(array $orderData)
    {
        if (!$this->hasApiKey()) {
            return [
                'success' => true,
                'id' => 'ord_mock_' . Str::random(10),
                'waybill_id' => 'MOCK-AWB-' . strtoupper(Str::random(10)),
                'status' => 'placed',
                'courier' => [
                    'company' => $orderData['courier_company'],
                    'type' => $orderData['courier_type'],
                    'waybill_id' => 'MOCK-AWB-' . strtoupper(Str::random(10))
                ],
                'message' => 'Simulasi Pengiriman Sukses (Mode Sandbox/Mock)'
            ];
        }

        try {
            $payload = [
                'shipper_contact_name' => $this->originName,
                'shipper_contact_phone' => $this->originPhone,
                'shipper_contact_email' => config('mail.from.address', 'admin@podgasm.com'),
                'origin_contact_name' => $this->originName,
                'origin_contact_phone' => $this->originPhone,
                'origin_address' => $this->originAddress,
                'origin_area_id' => $this->originAreaId,
                'origin_postal_code' => (int) $this->originPostalCode,
                
                'destination_contact_name' => $orderData['recipient_name'],
                'destination_contact_phone' => $orderData['recipient_phone'],
                'destination_contact_email' => $orderData['recipient_email'],
                'destination_address' => $orderData['recipient_address'],
                'destination_area_id' => $orderData['recipient_area_id'],
                
                'courier_company' => $orderData['courier_company'],
                'courier_type' => $orderData['courier_type'],
                'delivery_type' => 'now',
                'items' => $orderData['items']
            ];

            $response = $this->client()->post($this->baseUrl . '/v1/orders', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'id' => $data['id'] ?? null,
                    'waybill_id' => $data['courier']['waybill_id'] ?? null,
                    'status' => $data['status'] ?? 'placed',
                    'courier' => $data['courier'] ?? [],
                    'raw' => $data
                ];
            }

            Log::error('Biteship createOrder failed: ' . $response->body());
            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal menghubungi Biteship API.'
            ];
        } catch (\Exception $e) {
            Log::error('Biteship createOrder exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Melacak Status Paket Real-Time
     * 
     * @param string $waybillId Nomor Resi (atau tracking_id)
     * @param string $courierCode Kode kurir
     * @return array
     */
    public function getTracking(string $waybillId, string $courierCode)
    {
        if (!$this->hasApiKey() || str_starts_with($waybillId, 'MOCK-')) {
            return $this->getMockTracking($waybillId, $courierCode);
        }

        try {
            $response = $this->client()->get($this->baseUrl . "/v1/trackings/{$waybillId}/couriers/{$courierCode}");

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::error('Biteship getTracking failed: ' . $response->body());
            return $this->getMockTracking($waybillId, $courierCode);
        } catch (\Exception $e) {
            Log::error('Biteship getTracking exception: ' . $e->getMessage());
            return $this->getMockTracking($waybillId, $courierCode);
        }
    }

    /**
     * MOCK DATA: Daftar Wilayah
     */
    protected function getMockAreas(string $query)
    {
        $allAreas = [
            [
                'id' => 'IDNP3CL10',
                'name' => 'Kebayoran Baru, Jakarta Selatan, DKI Jakarta',
                'country' => 'ID',
                'province' => 'DKI Jakarta',
                'city' => 'Jakarta Selatan',
                'district' => 'Kebayoran Baru',
                'postal_code' => 12190
            ],
            [
                'id' => 'IDNP3CL12',
                'name' => 'Coblong, Bandung, Jawa Barat',
                'country' => 'ID',
                'province' => 'Jawa Barat',
                'city' => 'Bandung',
                'district' => 'Coblong',
                'postal_code' => 40135
            ],
            [
                'id' => 'IDNP3CL15',
                'name' => 'Tegalsari, Surabaya, Jawa Timur',
                'country' => 'ID',
                'province' => 'Jawa Timur',
                'city' => 'Surabaya',
                'district' => 'Tegalsari',
                'postal_code' => 60262
            ],
            [
                'id' => 'IDNP3CL18',
                'name' => 'Medan Baru, Medan, Sumatera Utara',
                'country' => 'ID',
                'province' => 'Sumatera Utara',
                'city' => 'Medan',
                'district' => 'Medan Baru',
                'postal_code' => 20152
            ],
            [
                'id' => 'IDNP3CL20',
                'name' => 'Danurejan, Yogyakarta, DI Yogyakarta',
                'country' => 'ID',
                'province' => 'DI Yogyakarta',
                'city' => 'Yogyakarta',
                'district' => 'Danurejan',
                'postal_code' => 55211
            ],
        ];

        // Filter berdasarkan query secara sederhana
        $filtered = array_filter($allAreas, function ($area) use ($query) {
            return Str::contains(strtolower($area['name']), strtolower($query));
        });

        return array_values($filtered);
    }

    /**
     * MOCK DATA: Tarif Ongkir
     */
    protected function getMockRates(string $destinationAreaId, int $totalWeight)
    {
        // Hitung pengali berdasarkan berat (per kg)
        $weightKg = max(1, ceil($totalWeight / 1000));
        
        $baseRates = [
            [
                'company' => 'jne',
                'type' => 'reg',
                'name' => 'JNE Regular',
                'price' => 9000 * $weightKg,
                'estimated_arrival' => '2 - 3 hari'
            ],
            [
                'company' => 'jne',
                'type' => 'yes',
                'name' => 'JNE Yakin Esok Sampai',
                'price' => 18000 * $weightKg,
                'estimated_arrival' => '1 - 1 hari'
            ],
            [
                'company' => 'sicepat',
                'type' => 'reg',
                'name' => 'SiCepat Reguler',
                'price' => 10000 * $weightKg,
                'estimated_arrival' => '1 - 2 hari'
            ],
            [
                'company' => 'jnt',
                'type' => 'ez',
                'name' => 'J&T EZ',
                'price' => 11000 * $weightKg,
                'estimated_arrival' => '2 - 3 hari'
            ]
        ];

        // Simulasi jika luar kota tarifnya agak mahal
        if ($destinationAreaId !== 'IDNP3CL10') {
            foreach ($baseRates as &$rate) {
                $rate['price'] += 7000 * $weightKg;
            }
        }

        return $baseRates;
    }

    /**
     * MOCK DATA: Tracking status
     */
    protected function getMockTracking(string $waybillId, string $courierCode)
    {
        return [
            'success' => true,
            'id' => 'track_mock_123',
            'waybill_id' => $waybillId,
            'courier' => [
                'company' => $courierCode,
                'name' => strtoupper($courierCode)
            ],
            'status' => 'delivery', // placed, picking_up, picked_up, delivering, delivered
            'history' => [
                [
                    'time' => now()->subHours(2)->format('Y-m-d H:i:s'),
                    'status' => 'delivering',
                    'note' => 'Paket sedang dibawa oleh kurir menuju alamat penerima.'
                ],
                [
                    'time' => now()->subHours(6)->format('Y-m-d H:i:s'),
                    'status' => 'picked_up',
                    'note' => 'Paket telah diserahterahkan ke kurir / Agen Terdekat.'
                ],
                [
                    'time' => now()->subDay()->format('Y-m-d H:i:s'),
                    'status' => 'placed',
                    'note' => 'Pengiriman telah berhasil dijadwalkan oleh admin toko.'
                ]
            ],
            'driver' => [
                'name' => 'Rahmat Hidayat',
                'phone' => '081299887766'
            ]
        ];
    }
}
