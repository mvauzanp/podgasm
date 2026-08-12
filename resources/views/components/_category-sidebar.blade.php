{{-- Minimalist Category Sidebar --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white" style="border: 1px solid #f1f5f9 !important;">
    <div class="card-header bg-white border-bottom border-light py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-dark text-uppercase tracking-wider fs-7">
            <i class="fas fa-layer-group text-primary me-2"></i> Kategori Produk
        </h6>
        <span class="badge bg-light text-muted rounded-pill px-2 py-1 small fw-normal">{{ $categories->count() }}</span>
    </div>
    
    <div class="list-group list-group-flush" id="categoryMenu">
        @foreach($categories->where('parent_id', null) as $parent)
            @php
                $isParentActive = isset($category) && ($category->id === $parent->id || $category->parent_id === $parent->id);
            @endphp
            <div class="list-group-item p-0 border-light">
                <a href="#collapse-{{ $parent->id }}" 
                   class="list-group-item-action d-flex justify-content-between align-items-center py-3 px-4 text-decoration-none fw-semibold border-0 {{ $isParentActive ? 'bg-light text-primary' : 'text-dark' }} sidebar-parent-link"
                   data-bs-toggle="collapse" 
                   role="button" 
                   aria-expanded="{{ $isParentActive ? 'true' : 'false' }}">
                    <span class="d-flex align-items-center">
                        <span class="icon-box me-3 rounded-circle d-inline-flex align-items-center justify-content-center {{ $isParentActive ? 'bg-primary text-white' : 'bg-light text-secondary' }}" style="width: 28px; height: 28px; font-size: 0.75rem;">
                            <i class="fas fa-folder"></i>
                        </span>
                        <span class="small">{{ $parent->nama_kategori }}</span>
                    </span>
                    <i class="fas fa-chevron-down fa-xs text-muted transition-transform" style="font-size: 0.7rem;"></i>
                </a>

                {{-- Sub-kategori Collapse --}}
                <div class="collapse {{ $isParentActive ? 'show' : '' }} bg-light bg-opacity-50" id="collapse-{{ $parent->id }}" data-bs-parent="#categoryMenu">
                    <div class="py-2 px-3">
                        @foreach($parent->children as $child)
                            @php
                                $isChildActive = isset($category) && $category->id === $child->id;
                            @endphp
                            <a href="{{ url('/category/' . $child->slug) }}" 
                               class="d-flex align-items-center justify-content-between py-2 px-3 rounded-3 text-decoration-none small transition-all mb-1 {{ $isChildActive ? 'bg-primary text-white fw-bold shadow-sm' : 'text-secondary hover-bg-white' }}">
                                <span>
                                    <i class="fas fa-minus me-2 opacity-50" style="font-size: 0.6rem;"></i> {{ $child->nama_kategori }}
                                </span>
                            </a>
                        @endforeach
                        
                        {{-- Opsi Lihat Semua --}}
                        <a href="{{ url('/category/' . $parent->slug) }}" 
                           class="d-block py-2 px-3 rounded-3 text-decoration-none small text-primary fw-semibold italic hover-bg-white mt-1">
                            <i class="fas fa-arrow-right me-1" style="font-size: 0.7rem;"></i> Lihat Semua {{ $parent->nama_kategori }}
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Stock Alert Widget untuk Admin --}}
@auth
    @if(Auth::user()->role == 'admin')
        <div class="card border-0 bg-warning bg-opacity-10 rounded-4 p-3 mb-4 text-warning-emphasis shadow-sm">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-exclamation-circle text-warning"></i>
                <small class="fw-bold">Safety Stock Alert</small>
            </div>
            <p class="mb-2 text-muted" style="font-size: 0.78rem;">Terdapat beberapa produk yang mendekati batas minimal stok.</p>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark px-3 py-1" style="font-size: 0.75rem;">
                Cek Dashboard <i class="fas fa-chevron-right ms-1" style="font-size: 0.65rem;"></i>
            </a>
        </div>
    @endif
@endauth

<style>
.sidebar-parent-link {
    transition: all 0.2s ease-in-out;
}
.sidebar-parent-link:hover {
    background-color: #f8fafc !important;
    color: var(--brand-color, #09afb9) !important;
}
.hover-bg-white:hover {
    background-color: #ffffff !important;
    color: var(--brand-color, #09afb9) !important;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
}
.fs-7 {
    font-size: 0.8rem;
}
</style>