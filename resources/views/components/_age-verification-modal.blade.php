{{-- Modal Verifikasi Usia 21+ (Clean Luxury Light Theme) --}}
<div id="ageVerificationOverlay" class="age-verification-overlay" style="display: none;">
    <div class="age-verification-card">
        {{-- Decorative Top Accent Line --}}
        <div class="age-card-accent"></div>

        {{-- Badge 21+ --}}
        <div class="age-badge-wrapper">
            <div class="age-badge-pulse"></div>
            <div class="age-badge-icon">
                <span>21+</span>
            </div>
        </div>

        {{-- Legal Tag Pill --}}
        <div class="age-compliance-pill">
            <i class="fas fa-shield-check text-teal me-1"></i>
            <span>Verifikasi Usia Resmi &bull; PP No. 28/2024</span>
        </div>
        
        <h3 class="age-title">Konfirmasi Usia Pengguna</h3>
        <p class="age-subtitle">
            Sesuai Peraturan Pemerintah RI, situs ini menyediakan produk khusus pengguna dewasa berusia <strong>21 tahun ke atas</strong>.
        </p>

        <div class="age-question-box">
            <div class="d-flex align-items-center justify-content-center gap-2">
                <i class="fas fa-user-shield text-teal fs-5"></i>
                <span>Apakah Anda telah berusia <strong>21 tahun atau lebih</strong>?</span>
            </div>
        </div>

        <div id="ageVerificationActions" class="age-buttons">
            <button type="button" id="btnAgeYes" class="btn btn-age-yes">
                <span>Ya, Saya 21+ Tahun</span>
                <i class="fas fa-arrow-right ms-2 fs-6"></i>
            </button>
            <button type="button" id="btnAgeNo" class="btn btn-age-no">
                <i class="fas fa-times-circle me-1"></i>
                <span>Tidak, Di Bawah 21</span>
            </button>
        </div>

        <div id="ageVerificationDenied" class="age-denied-message" style="display: none;">
            <div class="alert-denied-box">
                <div class="d-flex align-items-center gap-3 text-start">
                    <div class="denied-icon-wrap">
                        <i class="fas fa-user-lock"></i>
                    </div>
                    <div>
                        <strong class="d-block text-danger mb-1">Akses Tidak Diizinkan</strong>
                        <span class="small text-secondary">Mohon maaf, Anda belum memenuhi syarat usia legal. Anda akan dialihkan sebentar lagi...</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="age-footer-note">
            <i class="fas fa-lock me-1 text-secondary opacity-75"></i>
            Dengan melanjutkan, Anda mengonfirmasi kebenaran data usia Anda secara sah.
        </div>
    </div>
</div>

<style>
.age-verification-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    animation: ageOverlayFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes ageOverlayFadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.age-verification-card {
    position: relative;
    background: #ffffff;
    border-radius: 28px;
    max-width: 480px;
    width: 100%;
    padding: 36px 30px 28px 30px;
    text-align: center;
    box-shadow: 
        0 24px 60px -12px rgba(15, 23, 42, 0.3),
        0 0 0 1px rgba(226, 232, 240, 0.8),
        0 1px 3px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    animation: ageCardSlideUp 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes ageCardSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.age-card-accent {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #09afb9 0%, #0284c7 50%, #3b82f6 100%);
}

.age-badge-wrapper {
    position: relative;
    width: 84px;
    height: 84px;
    margin: 0 auto 16px auto;
    display: flex;
    align-items: center;
    justify-content: center;
}

.age-badge-pulse {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: rgba(9, 175, 185, 0.15);
    animation: agePulse 2.5s infinite cubic-bezier(0.4, 0, 0.6, 1);
}

@keyframes agePulse {
    0%, 100% { transform: scale(0.95); opacity: 0.8; }
    50% { transform: scale(1.18); opacity: 0.3; }
}

.age-badge-icon {
    position: relative;
    width: 72px;
    height: 72px;
    background: linear-gradient(135deg, #09afb9 0%, #0284c7 100%);
    color: #ffffff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 800;
    box-shadow: 0 10px 24px -4px rgba(9, 175, 185, 0.45);
    letter-spacing: -0.5px;
    border: 3px solid #ffffff;
}

.age-compliance-pill {
    display: inline-flex;
    align-items: center;
    padding: 5px 14px;
    background: #f0fdfa;
    border: 1px solid #ccfbf1;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #0f766e;
    margin-bottom: 16px;
}

.text-teal {
    color: #09afb9 !important;
}

.age-title {
    font-weight: 700;
    color: #0f172a;
    font-size: 1.45rem;
    letter-spacing: -0.3px;
    margin-bottom: 10px;
}

.age-subtitle {
    color: #64748b;
    font-size: 0.92rem;
    line-height: 1.6;
    margin-bottom: 22px;
    padding: 0 8px;
}

.age-question-box {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    color: #334155;
    padding: 14px 18px;
    border-radius: 16px;
    font-size: 0.94rem;
    margin-bottom: 24px;
}

.age-buttons {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
}

.btn-age-yes {
    flex: 1.3;
    position: relative;
    background: linear-gradient(135deg, #09afb9 0%, #0284c7 100%) !important;
    color: #ffffff !important;
    border: none !important;
    padding: 14px 20px !important;
    border-radius: 50px !important;
    font-weight: 600 !important;
    font-size: 0.95rem !important;
    box-shadow: 0 8px 20px -4px rgba(9, 175, 185, 0.45) !important;
    transition: all 0.25s ease !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    overflow: hidden;
}

.btn-age-yes::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
    transition: 0.5s;
}

.btn-age-yes:hover::before {
    left: 100%;
}

.btn-age-yes:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 12px 28px -4px rgba(9, 175, 185, 0.55) !important;
    color: #ffffff !important;
}

.btn-age-no {
    flex: 1;
    background: #f8fafc !important;
    color: #64748b !important;
    border: 1.5px solid #e2e8f0 !important;
    padding: 14px 18px !important;
    border-radius: 50px !important;
    font-weight: 600 !important;
    font-size: 0.92rem !important;
    transition: all 0.25s ease !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

.btn-age-no:hover {
    background: #fef2f2 !important;
    color: #ef4444 !important;
    border-color: #fca5a5 !important;
    transform: translateY(-1px) !important;
}

.alert-denied-box {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 16px;
    padding: 16px;
    margin-bottom: 20px;
    animation: ageOverlayFadeIn 0.3s ease forwards;
}

.denied-icon-wrap {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: #fee2e2;
    color: #ef4444;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.age-footer-note {
    font-size: 0.78rem;
    color: #94a3b8;
    line-height: 1.4;
}

@media (max-width: 576px) {
    .age-verification-card {
        padding: 28px 20px 22px 20px;
        border-radius: 24px;
    }
    .age-buttons {
        flex-direction: column;
    }
    .btn-age-yes, .btn-age-no {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const STORAGE_KEY = 'podgasm_age_verified_21';
    const COOKIE_NAME = 'podgasm_age_verified';
    
    // Helper check cookie
    function getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    // Helper set cookie (valid for 30 days)
    function setCookie(name, value, days) {
        const d = new Date();
        d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
        const expires = "expires=" + d.toUTCString();
        document.cookie = `${name}=${value}; ${expires}; path=/; SameSite=Lax`;
    }

    const isVerifiedLocal = localStorage.getItem(STORAGE_KEY) === 'true';
    const isVerifiedCookie = getCookie(COOKIE_NAME) === 'true';

    const overlay = document.getElementById('ageVerificationOverlay');
    const btnYes = document.getElementById('btnAgeYes');
    const btnNo = document.getElementById('btnAgeNo');
    const actionsDiv = document.getElementById('ageVerificationActions');
    const deniedDiv = document.getElementById('ageVerificationDenied');

    if (!isVerifiedLocal && !isVerifiedCookie) {
        // Tampilkan modal verifikasi usia
        overlay.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    if (btnYes) {
        btnYes.addEventListener('click', function() {
            // Simpan status verifikasi
            localStorage.setItem(STORAGE_KEY, 'true');
            setCookie(COOKIE_NAME, 'true', 30); // simpan 30 hari

            // Sembunyikan modal & kembalikan scroll
            overlay.style.animation = 'ageOverlayFadeIn 0.25s ease-in reverse forwards';
            setTimeout(function() {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }, 250);
        });
    }

    if (btnNo) {
        btnNo.addEventListener('click', function() {
            // Sembunyikan tombol & tampilkan pesan penolakan
            actionsDiv.style.display = 'none';
            deniedDiv.style.display = 'block';

            // Dialihkan ke Google setelah 2.5 detik
            setTimeout(function() {
                window.location.href = 'https://www.google.com';
            }, 2500);
        });
    }
});
</script>

