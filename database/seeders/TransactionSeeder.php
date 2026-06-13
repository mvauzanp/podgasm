<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        // Matikan foreign key constraint
        Schema::disableForeignKeyConstraints();

        // Hapus order lama untuk menghindari duplikasi
        OrderItem::truncate();
        Order::truncate();

        // Nyalakan kembali foreign key constraint
        Schema::enableForeignKeyConstraints();

        // Ambil data customers
        $customers = User::where('role', 'customer')->get();
        $products = Product::all();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Metode pembayaran yang valid
        $paymentMethods = ['cash', 'transfer', 'e-wallet'];
        $statuses = ['pending_payment', 'paid', 'shipped', 'delivered', 'cancelled'];

        // Buat 15 sample orders
        for ($i = 0; $i < 15; $i++) {
            // Pilih customer random
            $customer = $customers->random();

            // Pilih 2-4 produk random
            $selectedProducts = $products->random(rand(2, 4));

            // Hitung total
            $totalHarga = 0;
            $invoiceItems = [];
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $subtotal = $product->harga_jual * $quantity;
                $totalHarga += $subtotal;
                $invoiceItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $product->harga_jual,
                ];
            }

            // Tentukan status dan tanggal
            $status = $statuses[array_rand($statuses)];
            $createdAt = Carbon::now()->subDays(rand(1, 30));

            // Buat order
            $order = Order::create([
                'user_id' => $customer->id,
                'nama_penerima' => $customer->name,
                'email' => $customer->email,
                'no_telp' => '08' . rand(1000000000, 9999999999),
                'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'total_harga' => $totalHarga,
                'metode_pembayaran' => $paymentMethods[array_rand($paymentMethods)],
                'alamat_pengiriman' => 'Jl. Test No. ' . rand(1, 999) . ', Jakarta',
                'status' => $status,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            // Buat order items
            foreach ($invoiceItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        }
    }
}
