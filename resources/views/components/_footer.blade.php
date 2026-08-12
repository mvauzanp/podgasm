{{-- Footer & Health Warning Banner --}}
<footer class="mt-5 bg-dark text-white pt-4 pb-4 position-relative">
    {{-- Warning Banner Bar --}}
    <div class="nicotine-warning-bar py-3 px-3 mb-4 text-center">
        <div class="container">
            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                <span class="badge bg-danger rounded-circle px-2 py-1 fs-6 fw-bold">21+</span>
                <strong class="text-uppercase tracking-wider">Peringatan Kesehatan:</strong>
                <span>Produk ini mengandung nikotin. Nikotin adalah zat adiktif. HANYA UNTUK DEWASA BERUSIA 21 TAHUN KE ATAS. Tidak diperuntukkan bagi anak di bawah umur, wanita hamil, atau menyusui.</span>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row g-4 align-items-center text-center text-md-start">
            <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                    <img src="{{ asset('LogoPodgasm.png') }}" alt="Podgasm Logo" height="32" onerror="this.style.display='none'">
                    <h5 class="fw-bold mb-0 text-white">Podgasm Store</h5>
                </div>
                <p class="text-secondary small mb-0">
                    Penyedia produk vape & e-liquid berkualitas tinggi. Kepatuhan regulasi dan kenyamanan transaksi Anda adalah prioritas kami.
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-3 small text-secondary">
                    <span><i class="fas fa-shield-alt text-success me-1"></i> Transaksi Safe & Encrypted</span>
                    <span>|</span>
                    <button type="button" class="btn btn-link btn-sm text-secondary text-decoration-none p-0" id="btnResetAgeVerification">
                        <i class="fas fa-user-check me-1"></i> Status Usia: <span class="badge bg-success">21+ Verified</span>
                    </button>
                </div>
                <div class="text-secondary small mt-2">
                    &copy; {{ date('Y') }} Podgasm. All rights reserved. Sesuai PP No. 28 Tahun 2024 RI.
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
.nicotine-warning-bar {
    background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    border-top: 3px solid #ef4444;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    font-size: 0.85rem;
    color: #cbd5e1;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnReset = document.getElementById('btnResetAgeVerification');
    if (btnReset) {
        btnReset.addEventListener('click', function() {
            if (confirm('Apakah Anda ingin memverifikasi ulang usia Anda?')) {
                localStorage.removeItem('podgasm_age_verified_21');
                document.cookie = "podgasm_age_verified=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                window.location.reload();
            }
        });
    }
});
</script>
