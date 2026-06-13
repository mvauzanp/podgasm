<div class="card border-0 shadow-sm rounded-3 overflow-hidden">
    <div class="card-header bg-primary text-white py-3">
        <h6 class="mb-0 fw-bold"><i class="fas fa-list-ul me-2"></i> SEMUA KATEGORI</h6>
    </div>
    <div class="list-group list-group-flush" id="categoryMenu">
        @foreach($categories->where('parent_id', null) as $parent)
            {{-- Bagian Induk (Liquid, POD, dll) --}}
            <div class="list-group-item p-0 border-bottom">
                <a href="#collapse-{{ $parent->id }}" 
                   class="list-group-item-action d-flex justify-content-between align-items-center py-3 px-3 text-decoration-none fw-bold"
                   data-bs-toggle="collapse" 
                   role="button" 
                   aria-expanded="false">
                    <span>
                        <i class="fas fa-folder me-2 text-primary"></i> {{ $parent->nama_kategori }}
                    </span>
                    <i class="fas fa-chevron-down fa-xs text-muted"></i>
                </a>

                {{-- Bagian Anak (Sub-kategori seperti Freebase, Saltnic) --}}
                <div class="collapse bg-light" id="collapse-{{ $parent->id }}" data-bs-parent="#categoryMenu">
                    @foreach($parent->children as $child)
                        <a href="{{ url('/category/' . $child->slug) }}" 
                           class="list-group-item list-group-item-action py-2 ps-5 small border-0">
                            <i class="fas fa-caret-right me-2 text-muted"></i> {{ $child->nama_kategori }}
                        </a>
                    @endforeach
                    
                    {{-- Opsi Lihat Semua di kategori tersebut --}}
                    <a href="{{ url('/category/' . $parent->slug) }}" 
                       class="list-group-item list-group-item-action py-2 ps-5 small border-0 text-primary italic">
                        Lihat Semua {{ $parent->nama_kategori }}...
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Widget Tambahan: Informasi Stok (Opsi untuk Admin) --}}
@auth
    @if(Auth::user()->role == 'admin')
        <div class="alert alert-warning mt-3 border-0 shadow-sm">
            <small class="fw-bold"><i class="fas fa-exclamation-triangle"></i> ALERT STOK</small>
            <p class="mb-0 small">Ada produk yang menyentuh batas Safety Stock.</p>
            <a href="{{ route('admin.dashboard') }}" class="alert-link small">Cek Dashboard</a>
        </div>
    @endif
@endauth