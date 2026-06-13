<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\StockRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        // ✅ FITUR LAMA
        $stokKritis = Product::whereColumn('stok_aktual', '<=', 'nilai_ss')->get();
        $jumlahKritis = $stokKritis->count();

        $totalPenjualan = Transaction::whereDate('created_at', today())->sum('total_harga');

        $potensiKerugian = Product::where('tgl_expired', '<', now()->addDays(30))
                                    ->orWhere('tgl_cukai', '<', now()->subYear())
                                    ->get()
                                    ->sum(function($product) {
                                        return $product->stok_aktual * $product->harga_jual;
                                    });

        // ✅ FITUR BARU #1: STATISTIK PENDING ORDERS
        $pendingOrdersCount = Order::where('status', 'pending_payment')->count();
        $pendingOrdersValue = Order::where('status', 'pending_payment')->sum('total_harga');

        // ✅ FITUR BARU #2: REVENUE CHART (Last 7 Days)
        $last7Days = [];
        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $day = Carbon::now()->subDays($i)->format('D');
            
            $last7Days[] = $day;
            
            // Hitung revenue dari orders yang sudah paid
            $revenue = Order::where('status', 'paid')
                        ->whereDate('created_at', $date)
                        ->sum('total_harga');
            
            $revenueData[] = $revenue;
        }

        // ✅ FITUR BARU #3: TOP 5 SELLING PRODUCTS
        $topSellingProducts = OrderItem::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('COUNT(*) as order_count')
            )
            ->with('product')
            ->groupBy('product_id')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();

        // ✅ FITUR BARU #4: CUSTOMER ANALYTICS
        $totalCustomers = User::where('role', 'customer')->count();
        $totalBranches = User::where('role', 'branch')->count();
        
        // New customers this month
        $newCustomersThisMonth = User::where('role', 'customer')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // ✅ FITUR BARU #5: STOCK LEVEL MONITORING
        // Produk yang hampir expired (< 30 hari)
        $almostExpiredProducts = Product::where('tgl_expired', '<', now()->addDays(30))
                                        ->where('tgl_expired', '>', now())
                                        ->count();

        // Produk yang sudah expired
        $expiredProducts = Product::where('tgl_expired', '<', now())->count();

        // ✅ FITUR BARU #6: TOTAL ORDERS & STATUS DISTRIBUTION
        $totalOrders = Order::count();
        $paidOrders = Order::where('status', 'paid')->count();
        $cancelledOrders = Order::where('status', 'cancelled')->count();

        // ✅ FITUR BARU #7: LOW STOCK ALERTS
        $lowStockProducts = Product::whereColumn('stok_aktual', '<=', 'nilai_ss')
                                   ->where('stok_aktual', '>', 0)
                                   ->count();

        $outOfStockProducts = Product::where('stok_aktual', '<=', 0)->count();

        return view('pages.admin.dashboard', compact(
            // Fitur lama
            'stokKritis', 
            'jumlahKritis', 
            'totalPenjualan', 
            'potensiKerugian',
            
            // Fitur baru
            'pendingOrdersCount',
            'pendingOrdersValue',
            'last7Days',
            'revenueData',
            'topSellingProducts',
            'totalCustomers',
            'totalBranches',
            'newCustomersThisMonth',
            'almostExpiredProducts',
            'expiredProducts',
            'totalOrders',
            'paidOrders',
            'cancelledOrders',
            'lowStockProducts',
            'outOfStockProducts'
        ));
    }

    public function index()
    {
        $pendingCount = StockRequest::where('status', 'Pending')->count();
        
        if ($pendingCount > 0) {
            session()->now('warning', "Ada $pendingCount permintaan stok baru yang perlu diproses!");
        }

        return view('pages.admin.dashboard');
    }
}