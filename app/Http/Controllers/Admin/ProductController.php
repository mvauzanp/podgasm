<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        $query = Product::with('category');

        // Fitur Search (biar admin gampang cari barang)
        if ($request->has('search') && !empty($request->search)) {
            $query->where('nama_barang', 'like', '%' . $request->search . '%');
        }

        // Hitung statistik keseluruhan (sebelum dipaginasi)
        $totalProductsCount = Product::count();
        $lowStockCount = Product::whereRaw('stok_aktual <= nilai_ss')->count();
        $safeStockCount = Product::whereRaw('stok_aktual > nilai_ss')->count();

        $products = $query->latest()->paginate(10);
        
        return view('pages.admin.products.index', compact(
            'products',
            'totalProductsCount',
            'lowStockCount',
            'safeStockCount'
        ));
    }

    public function create()
    {
        $categories = Category::with('children')->get();
        return view('pages.admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        try {
            $variants = $request->input('variants');
            $variantFiles = $request->file('variants') ?? [];
            if (!empty($variants) && !empty($variantFiles)) {
                foreach ($variantFiles as $index => $fileData) {
                    if (isset($fileData['gambar'])) {
                        $variants[$index]['gambar'] = $fileData['gambar'];
                    }
                }
            }

            $product = $this->productService->storeProduct(
                $request->validated(),
                $request->file('gambar'),
                $variants,
                $request->has('has_variants') && $request->has('variants')
            );

            return redirect()->route('admin.products.index')
                ->with('success', 'Gacor! Produk ' . $product->nama_barang . ' berhasil ditambah.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error storing product in controller: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->except(['gambar', 'variants.*.gambar'])
            ]);
            return back()->withInput()->withErrors(['msg' => 'Gagal menyimpan produk: Terjadi kesalahan sistem.']);
        }
    }

    public function edit(Product $product)
    {
        $categories = Category::with('children')->get();
        // Eager load variants
        $product->load('variants');
        return view('pages.admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            $variants = $request->input('variants');
            $variantFiles = $request->file('variants') ?? [];
            if (!empty($variants) && !empty($variantFiles)) {
                foreach ($variantFiles as $index => $fileData) {
                    if (isset($fileData['gambar'])) {
                        $variants[$index]['gambar'] = $fileData['gambar'];
                    }
                }
            }

            $this->productService->updateProduct(
                $product,
                $request->validated(),
                $request->file('gambar'),
                $request->input('deleted_images'),
                $variants,
                $request->has('has_variants') && $request->has('variants')
            );

            return redirect()->route('admin.products.index')
                ->with('success', 'Produk berhasil diupdate!');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating product ID ' . $product->id . ' in controller: ' . $e->getMessage(), [
                'exception' => $e,
                'input' => $request->except(['gambar', 'variants.*.gambar'])
            ]);
            return back()->withInput()->withErrors(['msg' => 'Gagal memperbarui produk: Terjadi kesalahan sistem.']);
        }
    }

    public function destroy(Product $product)
    {
        try {
            foreach ($product->images as $img) {
                Storage::disk('public')->delete($img->path);
            }
            if ($product->gambar) {
                Storage::disk('public')->delete($product->gambar);
            }
            $product->delete();
            return redirect()->route('admin.products.index')->with('success', 'Produk dihapus!');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error deleting product ID ' . $product->id . ': ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return back()->withErrors(['msg' => 'Gagal menghapus produk: Terjadi kesalahan sistem.']);
        }
    }


    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Sheet 1: Template Produk
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Produk');
        
        // Headers
        $headers = [
            'Kode Barang',
            'Nama Barang',
            'Kategori / Sub-Kategori',
            'Deskripsi',
            'Harga Jual',
            'Harga Pokok',
            'Stok Aktual',
            'Nama Varian',
            'Tanggal Expired (YYYY-MM-DD)',
            'Tanggal Cukai (YYYY-MM-DD)'
        ];
        
        // Write headers
        foreach ($headers as $colIndex => $header) {
            $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . '1';
            $sheet->setCellValue($cellCoordinate, $header);
        }
        
        // Auto-fit column widths
        foreach (range(1, count($headers)) as $colIndex) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
        
        // Style headers
        $headerRange = 'A1:J1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true);
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('EAEAEA');
              
        // Add sample rows
        $samples = [
            [
                'BRG-001',
                'Liquid Saltnic Apple 30ml',
                'Saltnic',
                'Liquid rasa apel segar dengan kandungan nikotin 30mg',
                120000,
                80000,
                50,
                '', // No variant (simple product)
                '2027-12-31',
                '2026-06-01'
            ],
            [
                'BRG-002-RD',
                'Pod System X',
                'POD System',
                'Device Pod System X powerful',
                300000,
                200000,
                20,
                'Red Carbon', // Variant 1
                '',
                ''
            ],
            [
                'BRG-002-BL',
                'Pod System X',
                'POD System',
                'Device Pod System X powerful',
                300000,
                200000,
                15,
                'Blue Carbon', // Variant 2
                '',
                ''
            ]
        ];
        
        $row = 2;
        foreach ($samples as $sampleData) {
            foreach ($sampleData as $colIndex => $value) {
                $cellCoordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1) . $row;
                $sheet->setCellValue($cellCoordinate, $value);
            }
            $row++;
        }
        
        // Sheet 2: Daftar Kategori
        $categoriesSheet = $spreadsheet->createSheet();
        $categoriesSheet->setTitle('Daftar Kategori');
        $categoriesSheet->setCellValue('A1', 'ID Kategori');
        $categoriesSheet->setCellValue('B1', 'Nama Kategori');
        $categoriesSheet->setCellValue('C1', 'Kategori Induk (Parent)');
        $categoriesSheet->getStyle('A1:C1')->getFont()->setBold(true);
        $categoriesSheet->getStyle('A1:C1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                        ->getStartColor()->setARGB('EAEAEA');
                        
        $categories = \App\Models\Category::with('parent')->get();
        $catRow = 2;
        foreach ($categories as $category) {
            $categoriesSheet->setCellValue('A' . $catRow, $category->id);
            $categoriesSheet->setCellValue('B' . $catRow, $category->nama_kategori);
            $categoriesSheet->setCellValue('C' . $catRow, $category->parent ? $category->parent->nama_kategori : '-');
            $catRow++;
        }
        
        $categoriesSheet->getColumnDimension('A')->setAutoSize(true);
        $categoriesSheet->getColumnDimension('B')->setAutoSize(true);
        $categoriesSheet->getColumnDimension('C')->setAutoSize(true);
        
        // Set first sheet active
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Clear buffer
        if (ob_get_contents()) ob_end_clean();
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_bulk_product_podgasm.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_excel' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);
        
        try {
            $file = $request->file('file_excel');
            $importedCount = $this->productService->importProductsFromExcel($file->getRealPath());
            
            return redirect()->route('admin.products.index')
                ->with('success', 'Gacor! Berhasil mengimport ' . $importedCount . ' produk dari Excel.');
                
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            // Jika error message dipisahkan oleh newline (dari validasi Service Layer), pecah menjadi array
            if (str_contains($errorMsg, "\n")) {
                $errors = explode("\n", $errorMsg);
                return back()->withErrors($errors);
            }
            
            \Illuminate\Support\Facades\Log::error('Error importing products via Excel: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            return back()->withErrors(['msg' => $errorMsg]);
        }
    }
}