@extends('layouts.auth')

@section('content_auth')
<div class="w-100 mx-auto" style="max-width: 480px;">
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="{{ route('home') }}" class="back-home-btn">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali ke Beranda</span>
        </a>
    </div>

    <div class="card auth-card shadow-lg border-0 rounded-4 mx-auto" style="overflow: hidden;">
    <div class="p-4 p-md-4">
        {{-- LOGO --}}
        <div class="text-center mb-3">
            <a href="{{ route('home') }}">
                <img src="{{ asset('LogoPodgasm.png') }}" alt="Podgasm Logo" style="height: 42px; width: auto;" class="object-fit-contain">
            </a>
        </div>

        <div class="text-center mb-3">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary rounded-circle mb-2" style="width: 52px; height: 52px;">
                <i class="fas fa-envelope-open-text fa-lg text-primary"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1" style="font-size: 1.25rem;">Verifikasi Email Anda</h5>
            <p class="text-muted mb-0" style="font-size: 0.82rem;">
                Kami telah mengirimkan 6 digit kode OTP ke:<br>
                <strong class="text-dark">{{ $email }}</strong>
            </p>
        </div>

        {{-- ALERT MESSAGES --}}
        @if (session('success'))
            <div class="alert alert-success rounded-3 border-0 shadow-sm mb-3 py-2 px-3 small text-center" style="font-size: 0.8rem;">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning rounded-3 border-0 shadow-sm mb-3 py-2 px-3 small text-center" style="font-size: 0.8rem;">
                <i class="fas fa-exclamation-triangle me-1"></i> {{ session('warning') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger rounded-3 border-0 shadow-sm mb-3 py-2 px-3 small text-center" style="font-size: 0.8rem;">
                <i class="fas fa-times-circle me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register.verify.process') }}" method="POST" id="otpForm">
            @csrf
            <input type="hidden" name="otp" id="fullOtpInput">

            {{-- 6 DIGIT OTP INPUT BOXES --}}
            <div class="d-flex justify-content-center gap-2 mb-3">
                <input type="text" maxlength="1" class="form-control text-center otp-digit fw-bold" inputmode="numeric" pattern="[0-9]*" autofocus autocomplete="off">
                <input type="text" maxlength="1" class="form-control text-center otp-digit fw-bold" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                <input type="text" maxlength="1" class="form-control text-center otp-digit fw-bold" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                <input type="text" maxlength="1" class="form-control text-center otp-digit fw-bold" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                <input type="text" maxlength="1" class="form-control text-center otp-digit fw-bold" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
                <input type="text" maxlength="1" class="form-control text-center otp-digit fw-bold" inputmode="numeric" pattern="[0-9]*" autocomplete="off">
            </div>

            {{-- EXPIRATION COUNTDOWN --}}
            <div class="text-center mb-3">
                <span class="text-muted" style="font-size: 0.78rem;">
                    <i class="fas fa-stopwatch me-1"></i> Masa berlaku kode: 
                    <strong id="expireTimer" class="text-danger">05:00</strong>
                </span>
            </div>

            <button type="submit" id="submitBtn" class="btn btn-primary w-100 rounded-3 fw-bold shadow-sm mb-3 py-2" style="font-size: 0.92rem; height: 44px;" disabled>
                Verifikasi &amp; Buat Akun
            </button>
        </form>

        {{-- RESEND OTP FORM --}}
        <div class="text-center pt-1">
            <form action="{{ route('register.resend.otp') }}" method="POST" id="resendForm">
                @csrf
                <p class="text-muted mb-1" style="font-size: 0.8rem;">Tidak menerima email verifikasi?</p>
                <button type="submit" id="resendBtn" class="btn btn-link text-decoration-none fw-semibold p-0 text-primary" style="font-size: 0.8rem;" disabled>
                    Kirim Ulang Kode (<span id="resendTimer">60</span>s)
                </button>
            </form>
        </div>

        <div class="text-center mt-3 pt-2 border-top">
            <a href="{{ route('register') }}" class="text-muted text-decoration-none" style="font-size: 0.8rem;">
                <i class="fas fa-arrow-left me-1"></i> Ganti Email / Daftar Ulang
            </a>
        </div>
    </div>
</div>
</div>

<style>
    .otp-digit {
        width: 44px;
        height: 50px;
        font-size: 1.35rem;
        border-radius: 10px;
        border: 2px solid #e2e8f0;
        background-color: #f8fafc;
        transition: all 0.2s ease;
    }
    .otp-digit:focus {
        border-color: #09afb9;
        background-color: #ffffff;
        box-shadow: 0 0 0 3px rgba(9, 175, 185, 0.15);
        outline: none;
    }
    .otp-digit.filled {
        border-color: #0284c7;
        background-color: #f0fdfa;
    }

    @media (max-width: 420px) {
        .otp-digit {
            width: 38px;
            height: 46px;
            font-size: 1.15rem;
            border-radius: 8px;
        }
    }

    @media (max-width: 350px) {
        .otp-digit {
            width: 32px;
            height: 42px;
            font-size: 1rem;
            border-radius: 6px;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = document.querySelectorAll('.otp-digit');
        const hiddenInput = document.getElementById('fullOtpInput');
        const submitBtn = document.getElementById('submitBtn');
        const otpForm = document.getElementById('otpForm');

        function updateFullOtp() {
            let code = '';
            inputs.forEach(input => {
                code += input.value;
                if (input.value) {
                    input.classList.add('filled');
                } else {
                    input.classList.remove('filled');
                }
            });
            hiddenInput.value = code;
            submitBtn.disabled = (code.length !== 6);
        }

        inputs.forEach((input, index) => {
            input.addEventListener('input', function (e) {
                // Hanya terima angka
                this.value = this.value.replace(/[^0-9]/g, '');
                
                if (this.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
                updateFullOtp();
                if (hiddenInput.value.length === 6) {
                    otpForm.submit();
                }
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace') {
                    if (!this.value && index > 0) {
                        inputs[index - 1].focus();
                    }
                }
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                const pasteData = (e.clipboardData || window.clipboardData).getData('text').trim();
                const cleanData = pasteData.replace(/[^0-9]/g, '').slice(0, 6);
                
                cleanData.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                    }
                });

                if (cleanData.length > 0) {
                    const focusIndex = Math.min(cleanData.length, inputs.length - 1);
                    inputs[focusIndex].focus();
                }
                updateFullOtp();
                if (cleanData.length === 6) {
                    otpForm.submit();
                }
            });
        });

        // 1. Resend Button Cooldown (60 detik)
        let resendLeft = 60;
        const resendBtn = document.getElementById('resendBtn');
        const resendTimerSpan = document.getElementById('resendTimer');

        const resendInterval = setInterval(() => {
            resendLeft--;
            if (resendLeft <= 0) {
                clearInterval(resendInterval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Kirim Ulang Kode Sekarang';
            } else {
                resendTimerSpan.textContent = resendLeft;
            }
        }, 1000);

        // 2. OTP Expiry Countdown (5 Menit)
        let expireSeconds = 300;
        const expireTimerSpan = document.getElementById('expireTimer');

        const expireInterval = setInterval(() => {
            expireSeconds--;
            if (expireSeconds <= 0) {
                clearInterval(expireInterval);
                expireTimerSpan.textContent = '00:00 (Kedaluwarsa)';
                submitBtn.disabled = true;
            } else {
                const m = Math.floor(expireSeconds / 60).toString().padStart(2, '0');
                const s = (expireSeconds % 60).toString().padStart(2, '0');
                expireTimerSpan.textContent = `${m}:${s}`;
            }
        }, 1000);
    });
</script>
@endsection
