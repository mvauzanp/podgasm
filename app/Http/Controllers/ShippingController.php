<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        $biteshipItems = [];
        foreach ($items as $item) {
            $biteshipItems[] = [
                'name' => $item->product->nama_barang . ($item->variant ? ' (' . $item->variant->nama_varian . ')' : ''),
                'price' => $item->price,
                'quantity' => $item->quantity,
                'weight' => 200 // default weight 200gr per item
            ];
        }

        $biteship = app(BiteshipService::class);
        $rates = $biteship->getRates($request->destination_area_id, $biteshipItems);

        return response()->json($rates);
    }
}
