{{-- HERO CATEGORY SECTION (Full-Width Clean Header & Smooth Drag Slider) --}}
<div class="category-hero-section mb-4 mb-md-5">
    {{-- Header: Judul di Kiri, Tombol Navigasi di Kanan --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-baseline gap-3 flex-wrap">
            <h2 class="surfeit-category-title text-dark fw-black mb-0">
                {{ isset($category) ? $category->nama_kategori : 'Kategori Produk' }}
            </h2>
            <span class="text-secondary small d-none d-md-inline" style="font-size: 0.88rem;">
                {{ isset($category) ? 'Jelajahi produk ' . $category->nama_kategori : 'Pilihan kategori lengkap Podgasm' }}
            </span>
        </div>

        {{-- Tombol Navigasi Geser Kiri / Kanan --}}
        <div id="categoryScrollNav" class="d-flex gap-2 align-items-center flex-shrink-0 ms-2">
            <button type="button" class="btn btn-sm rounded-circle category-scroll-btn shadow-sm" id="btnScrollCatLeft" style="width: 38px; height: 38px; padding: 0;" title="Geser Kiri">
                <i class="fas fa-chevron-left small"></i>
            </button>
            <button type="button" class="btn btn-sm rounded-circle category-scroll-btn shadow-sm" id="btnScrollCatRight" style="width: 38px; height: 38px; padding: 0;" title="Geser Kanan">
                <i class="fas fa-chevron-right small"></i>
            </button>
        </div>
    </div>

    {{-- Slider Cards: 100% Full Width --}}
    <div id="categoryScrollContainer" class="d-flex gap-3 overflow-x-auto pb-2 no-scrollbar justify-content-start category-drag-scroll" style="cursor: grab; scroll-behavior: smooth;">
        {{-- Opsi "Semua Produk" jika sedang membuka kategori tertentu --}}
        @if(isset($category) || request()->routeIs('public.category') || request()->filled('category'))
            <a href="{{ route('home') }}" class="text-decoration-none text-dark flex-shrink-0 category-slide-link" draggable="false">
                <div class="surfeit-subcategory-card border rounded-3 overflow-hidden text-center" style="width: 124px; transition: all 0.25s ease;">
                    <div class="position-relative overflow-hidden p-2 d-flex align-items-center justify-content-center category-icon-box" style="height: 82px;">
                        <i class="fas fa-th-large opacity-50 text-dark fa-2x"></i>
                    </div>
                    <div class="p-2 text-center category-card-label">
                        <span class="fw-bold small d-block text-dark text-truncate" style="font-size: 0.78rem;">
                            Semua Produk
                        </span>
                    </div>
                </div>
            </a>
        @endif

        @foreach($categories->where('parent_id', null) as $cat)
            @php
                $isCatActive = (isset($category) && ($category->id === $cat->id || $category->parent_id === $cat->id)) 
                               || request()->is('category/' . $cat->slug) 
                               || request('category') === $cat->slug;
            @endphp
            <a href="{{ route('public.category', $cat->slug) }}" class="text-decoration-none text-dark flex-shrink-0 category-slide-link" draggable="false">
                <div class="surfeit-subcategory-card border rounded-3 overflow-hidden text-center {{ $isCatActive ? 'active-category-card' : '' }}" style="width: 124px; transition: all 0.25s ease;">
                    <div class="position-relative overflow-hidden p-2 d-flex align-items-center justify-content-center category-icon-box" style="height: 82px;">
                        @php
                            $iconUrl = null;
                            $iconMap = [
                                'saltnic-liquid' => 'saltnic',
                                'freebase-liquid' => 'freebase',
                                'aksesoris' => 'accessories',
                                'cartridge' => 'close-system',
                                'pod-device' => 'pod',
                                'disposable-pod' => 'pod',
                                'pod-close-system' => 'close-system',
                                'mod-device' => 'mod',
                                'occ' => 'atomizer',
                                'aio' => 'aio',
                                'veev' => 'close-system',
                                'iluma-iqos' => 'pod',
                                'bondsblends-by-iqos' => 'pod',
                                'terea-iqos' => 'pod',
                                'coil-prebuild' => 'atomizer',
                            ];
                            $possibleNames = array_unique([
                                $iconMap[$cat->slug] ?? null,
                                $cat->slug,
                                str_replace('-', ' ', $cat->slug),
                                str_replace(' ', '-', $cat->slug),
                                explode('-', $cat->slug)[0] ?? null,
                                \Illuminate\Support\Str::slug($cat->nama_kategori),
                                strtolower($cat->nama_kategori),
                            ]);
                            $extensions = ['png', 'svg', 'webp', 'jpg', 'jpeg'];
                            foreach ($possibleNames as $name) {
                                if (!$name) continue;
                                foreach ($extensions as $ext) {
                                    $relPath = "images/categories/{$name}.{$ext}";
                                    if (file_exists(public_path($relPath))) {
                                        $iconUrl = asset($relPath);
                                        break 2;
                                    }
                                }
                            }
                        @endphp

                        @if($iconUrl)
                            <img src="{{ $iconUrl }}" alt="{{ $cat->nama_kategori }}" class="img-fluid" style="max-height: 52px; object-fit: contain; pointer-events: none;" draggable="false">
                        @else
                            <i class="fas fa-box opacity-30 text-dark fa-lg"></i>
                        @endif
                    </div>
                    <div class="p-2 text-center category-card-label">
                        <span class="fw-bold small d-block text-dark text-truncate" style="font-size: 0.78rem;">
                            {{ $cat->nama_kategori }}
                        </span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>
