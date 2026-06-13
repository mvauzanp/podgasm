@extends('layouts.frontend')

@section('title', 'Request Stok ke Pusat')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow border-0 rounded-4">
            <div class="card-header bg-primary text-white py-3">
                <h5 class="mb-0 fw-bold"><i class="fas fa-truck-loading me-2"></i> Form Permintaan Stok Baru</h5>
            </div>
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        <i class="fas fa-exclamation-triangle me-2"></i> {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('branch.request.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-bold">Pilih Produk</label>
                        <select name="product_selection" class="form-select rounded-3" required>
                            <option value="" selected disabled>-- Cari Produk di Pusat --</option>
                            
                            @foreach($products as $p)
                                @if($p->hasVariants())
                                    @foreach($p->variants as $v)
                                        <option value="variant_{{ $v->id }}">
                                            {{ $p->nama_barang }} - {{ $v->nama_varian }} (Stok Pusat: {{ $v->stok_aktual }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="product_{{ $p->id }}">
                                        {{ $p->nama_barang }} (Stok Pusat: {{ $p->stok_aktual }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Jumlah Permintaan (Unit)</label>
                            <input type="number" name="jumlah" class="form-control form-control-lg shadow-sm" placeholder="Contoh: 50" min="1" required>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label class="form-label fw-bold">Prioritas</label>
                            <select name="prioritas" class="form-select form-select-lg shadow-sm">
                                <option value="Normal">Normal</option>
                                <option value="Urgent">Urgent (Stok Hampir Habis)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Catatan Tambahan (Opsional)</label>
                        <textarea name="keterangan" class="form-control shadow-sm" rows="3" placeholder="Contoh: Untuk persiapan event weekend"></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Permintaan Stok
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection