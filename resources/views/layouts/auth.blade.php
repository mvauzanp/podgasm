<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Podgasm System</title>
    
    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('LogoPodgasm.png') }}">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background: #f4f7f6; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px 12px; margin: 0; }
        .auth-card { width: 100%; border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: #ffffff; }
        .back-home-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #64748b;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            transition: all 0.2s ease;
        }
        .back-home-btn:hover {
            color: #0284c7;
            border-color: #0284c7;
            background: #f0fdfa;
            transform: translateX(-2px);
        }
    </style>
</head>
<body>
    @yield('content_auth')
</body>
</html>