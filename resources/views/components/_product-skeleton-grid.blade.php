{{-- Skeleton Loading Placeholder Grid --}}
<div id="productGridSkeleton" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 g-xl-5 mb-5">
    @for($i = 0; $i < 8; $i++)
    <div class="col">
        <div class="product-skeleton-card">
            {{-- Skeleton Floating Image Box --}}
            <div class="skeleton-shimmer product-skeleton-img mb-3"></div>

            {{-- Skeleton Category Line --}}
            <div class="skeleton-shimmer product-skeleton-text" style="width: 35%; height: 12px; margin-bottom: 6px;"></div>

            {{-- Skeleton Product Title --}}
            <div class="skeleton-shimmer product-skeleton-text" style="width: 80%; height: 16px; margin-bottom: 8px;"></div>

            {{-- Skeleton Price Line --}}
            <div class="skeleton-shimmer product-skeleton-text" style="width: 50%; height: 14px;"></div>
        </div>
    </div>
    @endfor
</div>
