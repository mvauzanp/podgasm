@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0">Wishlist Saya</h3>
        <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm rounded-pill">
            <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
        </a>
    </div>

    @if(session('wishlist') && count(session('wishlist')) > 0)
    <div class="row g-4">
        @foreach(session('wishlist') as $id => $details)
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                {{-- Gambar Produk --}}
                <div class="bg-light" style="height: 200px; overflow: hidden; position: relative;">
                    <img src="{{ asset('storage/' . $details['gambar']) }}" class="w-100 h-100 object-fit-cover">
                    
                    {{-- Tombol Hapus --}}
                    <form action="{{ route('wishlist.remove', $id) }}" method="POST" style="position: absolute; top: 10px; right: 10px;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-white btn-sm rounded-circle shadow-sm text-danger">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>

                <div class="card-body p-3">
                    <h6 class="fw-bold text-dark text-truncate mb-1">{{ $details['nama'] }}</h6>
                    <p class="text-primary fw-bold mb-3">Rp {{ number_format($details['harga'], 0, ',', '.') }}</p>
                    
                    {{-- Tombol Pindah ke Keranjang --}}
                    <button type="button" 
                        class="btn btn-primary w-100 rounded-pill fw-bold btn-sm"
                        data-bs-toggle="modal" 
                        data-bs-target="#modalTambah{{ $id }}">
                        <i class="fas fa-cart-plus me-2"></i>Beli Sekarang
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Tambah ke Keranjang (Sama seperti di Index) --}}
        <div class="modal fade" id="modalTambah{{ $id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content border-0 shadow rounded-4">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="fw-bold mb-0">Atur Jumlah</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('cart.add', $id) }}" method="POST">
                        @csrf
                        <div class="modal-body text-center">
                            <input type="number" name="quantity" class="form-control text-center fw-bold" value="1" min="1" required>
                            <small class="text-muted d-block mt-2">Tentukan jumlah unit</small>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">Masukkan Keranjang</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-5">
        <div class="mb-3">
            <i class="far fa-heart fa-4x text-muted opacity-25"></i>
        </div>
        <h5 class="text-muted">Wishlist Anda masih kosong</h5>
        <p class="small text-muted">Simpan barang impianmu di sini untuk dibeli nanti.</p>
        <a href="{{ route('home') }}" class="btn btn-primary px-4 rounded-pill mt-3">Cari Produk</a>
    </div>
    @endif
</div>
@endsection