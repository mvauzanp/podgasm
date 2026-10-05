<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kode Verifikasi Podgasm</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f4f7f6;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #333333;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 580px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid #eef2f5;
        }
        .header {
            background: linear-gradient(135deg, #09afb9 0%, #0284c7 100%);
            padding: 35px 25px;
            text-align: center;
            color: #ffffff;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.9;
        }
        .body-content {
            padding: 35px 30px;
        }
        .greeting {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }
        .text {
            font-size: 15px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .otp-box-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            display: inline-block;
            background: #f0fdfa;
            border: 2px dashed #09afb9;
            border-radius: 12px;
            padding: 16px 36px;
            font-size: 34px;
            font-weight: 800;
            letter-spacing: 10px;
            color: #0f766e;
        }
        .meta-info {
            background-color: #f8fafc;
            border-left: 4px solid #09afb9;
            padding: 14px 18px;
            border-radius: 0 8px 8px 0;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            margin-bottom: 24px;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .footer a {
            color: #0284c7;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PODGASM</h1>
            <p>Retail & Wholesale Vape Store</p>
        </div>
        <div class="body-content">
            <div class="greeting">Halo, {{ $name }}! 👋</div>
            <p class="text">
                Terima kasih telah mendaftar di <strong>Podgasm</strong>. Untuk menyelesaikan pembuatan akun Anda dan memastikan keamanan data, silakan gunakan kode verifikasi (OTP) berikut:
            </p>

            <div class="otp-box-wrapper">
                <div class="otp-code">{{ $otp }}</div>
            </div>

            <div class="meta-info">
                ⏱️ Kode OTP ini berlaku selama <strong>5 menit</strong>.<br>
                🔒 <strong>PENTING:</strong> Jangan berikan kode ini kepada siapa pun, termasuk pihak yang mengatasnamakan Podgasm.
            </div>

            <p class="text" style="font-size: 13px; margin-bottom: 0;">
                Jika Anda tidak merasa melakukan pendaftaran akun di Podgasm, Anda dapat mengabaikan email ini dengan aman.
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Podgasm Indonesia. All rights reserved.<br>
            Jl. Kertajaya No.188, Kertajaya, Kec. Gubeng, Surabaya, Jawa Timur 60281
        </div>
    </div>
</body>
</html>
