<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login &mdash; Kasir Koperasi Aljida</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }

        body {
            min-height: 100vh;
            background: linear-gradient(160deg, #0A1A0F 0%, #122B1E 45%, #1B3A2A 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background layers */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image:
                radial-gradient(circle at 15% 85%, rgba(201,169,79,0.12) 0%, transparent 45%),
                radial-gradient(circle at 85% 15%, rgba(27,58,42,0.6) 0%, transparent 50%),
                radial-gradient(circle at 50% 50%, rgba(18,43,30,0.4) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Subtle pattern overlay */
        body::after {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: repeating-linear-gradient(
                45deg,
                transparent,
                transparent 40px,
                rgba(201,169,79,0.03) 40px,
                rgba(201,169,79,0.03) 41px
            );
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        /* Brand Header */
        .brand-header {
            text-align: center;
            margin-bottom: 28px;
            animation: fadeInDown 0.55s ease-out;
        }

        .logo-wrapper {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 16px;
        }

        .logo-wrapper img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            filter: drop-shadow(0 6px 20px rgba(201,169,79,0.45));
        }

        .logo-fallback {
            width: 88px;
            height: 88px;
            background: linear-gradient(135deg, #1B3A2A, #2D5C41);
            border: 3px solid #C9A94F;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            color: #C9A94F;
            box-shadow: 0 8px 28px rgba(201,169,79,0.35), 0 0 0 6px rgba(201,169,79,0.1);
        }

        .brand-header h1 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.2px;
            line-height: 1.3;
        }

        .brand-header .koperasi-name {
            font-size: 0.82rem;
            color: #C9A94F;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .brand-divider {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px auto 0;
            max-width: 220px;
        }

        .brand-divider::before,
        .brand-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(201,169,79,0.4);
        }

        .brand-divider span {
            font-size: 0.65rem;
            color: rgba(201,169,79,0.6);
            letter-spacing: 1.5px;
            white-space: nowrap;
        }

        /* Login Card */
        .login-card {
            background: #fff;
            border-radius: 20px;
            padding: 38px 38px 32px;
            box-shadow:
                0 30px 70px rgba(0,0,0,0.4),
                0 0 0 1px rgba(201,169,79,0.15);
            animation: fadeInUp 0.55s ease-out;
        }

        .login-card h2 {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1B3A2A;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .login-card h2 .icon-login {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #1B3A2A, #2D5C41);
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.85rem;
        }

        .form-group { margin-bottom: 18px; }

        .form-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 7px;
        }

        .input-wrapper { position: relative; }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #4A7A5A;
            font-size: 0.88rem;
        }

        .form-group input[type="email"],
        .form-group input[type="password"],
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #C8DDD3;
            border-radius: 10px;
            font-size: 0.9rem;
            color: #333;
            background: #FAFCFB;
            transition: all 0.2s ease;
            outline: none;
        }

        .form-group input:focus {
            border-color: #1B3A2A;
            box-shadow: 0 0 0 3px rgba(27,58,42,0.1);
            background: #fff;
        }

        .form-group .error-msg {
            color: #C62828;
            font-size: 0.78rem;
            margin-top: 5px;
        }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
        }

        .remember-row input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #1B3A2A;
            cursor: pointer;
        }

        .remember-row label {
            font-size: 0.83rem;
            color: #666;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1B3A2A, #C9A94F);
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.25s ease;
            letter-spacing: 0.3px;
            box-shadow: 0 5px 18px rgba(27,58,42,0.4);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #0F2318, #1B3A2A);
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(27,58,42,0.5);
        }

        .btn-login:active { transform: scale(0.98); }

        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            font-size: 0.82rem;
            color: #4A7A5A;
            text-decoration: none;
            transition: color 0.2s;
        }

        .forgot-link:hover { color: #1B3A2A; }

        .session-status {
            background: #E8F5E9;
            color: #2E7D32;
            border-left: 4px solid #43A047;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.83rem;
            margin-bottom: 18px;
        }

        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .copyright {
            text-align: center;
            margin-top: 22px;
            font-size: 0.72rem;
            color: rgba(255,255,255,0.28);
            letter-spacing: 0.3px;
        }

        .copyright span {
            color: rgba(201,169,79,0.5);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Brand Header -->
        <div class="brand-header">
            <div class="logo-wrapper">
                @php $logoPath = public_path('images/logo-koperasi.png'); @endphp
                @if(file_exists($logoPath))
                    <img src="{{ asset('images/logo-koperasi.png') }}" alt="Logo Koperasi Aljida">
                @else
                    <div class="logo-fallback">
                        <i class="fas fa-store-alt"></i>
                    </div>
                @endif
            </div>
            <h1>Koperasi Aljida<br>Sukses Gemilang</h1>
            <div class="brand-divider"><span>PEGANDON &bull; KENDAL</span></div>
            <p class="koperasi-name">Sistem Kasir & Manajemen Stok</p>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            <h2>
                <span class="icon-login"><i class="fas fa-sign-in-alt"></i></span>
                Masuk ke Sistem
            </h2>
            {{ $slot }}
        </div>

        <div class="copyright">
            &copy; {{ date('Y') }} <span>Koperasi Aljida Sukses Gemilang</span> &bull; All rights reserved
        </div>
    </div>
</body>
</html>
