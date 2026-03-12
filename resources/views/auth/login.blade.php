<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; HRM System</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1e2a3b 0%, #2d3f57 50%, #1e2a3b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: 16px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, .4);
            overflow: hidden;
        }

        .login-header {
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            padding: 36px 32px 28px;
            text-align: center;
        }

        .login-header .brand-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, .15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #fff;
            margin: 0 auto 16px;
            backdrop-filter: blur(8px);
        }

        .login-header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #fff;
            margin: 0;
        }

        .login-header p {
            font-size: 13px;
            color: rgba(255, 255, 255, .65);
            margin: 4px 0 0;
        }

        .login-body {
            background: #fff;
            padding: 32px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 8px;
            border-color: #d1d5db;
            font-size: 14px;
            padding: 10px 14px;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, .15);
        }

        .input-group-text {
            background: #f9fafb;
            border-radius: 8px 0 0 8px !important;
            border-color: #d1d5db;
            color: #6b7280;
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0 !important;
        }

        .btn-login {
            width: 100%;
            padding: 11px;
            font-weight: 600;
            font-size: 15px;
            border-radius: 8px;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            border: none;
            color: #fff;
            transition: opacity .2s, transform .1s;
        }

        .btn-login:hover {
            opacity: .9;
            color: #fff;
        }

        .btn-login:active {
            transform: scale(.99);
        }

        .forgot-link {
            font-size: 13px;
            color: #6b7280;
            text-decoration: none;
        }

        .forgot-link:hover {
            color: #3b82f6;
        }

        .invalid-feedback {
            font-size: 12px;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #dc2626;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="login-card card">

    {{-- Header --}}
    <div class="login-header">
        <div class="brand-icon">
            <i class="bi bi-grid-1x2-fill"></i>
        </div>
        <h1>HRM System</h1>
        <p>Sistem Informasi Kepegawaian</p>
    </div>

    {{-- Body --}}
    <div class="login-body">

        {{-- Session status (e.g. inactive account message) --}}
        @if(session('status'))
            <div class="alert-error mb-3">
                <i class="bi bi-info-circle me-1"></i>
                {{ session('status') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if($errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Username --}}
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input type="text" id="username" name="username"
                           class="form-control @error('username') is-invalid @enderror"
                           value="{{ old('username') }}"
                           placeholder="Masukkan username"
                           required autofocus autocomplete="username">
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" id="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="••••••••"
                           required autocomplete="current-password">
                </div>
            </div>

            {{-- Remember me --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember" style="font-size:13px;color:#6b7280;">
                        Ingat saya
                    </label>
                </div>

                @if(Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="forgot-link">
                        Lupa password?
                    </a>
                @endif
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>
                Masuk ke Sistem
            </button>

        </form>

        <p class="text-center text-muted mt-4 mb-0" style="font-size:12px;">
            &copy; {{ date('Y') }} HRM System &middot; Internal Use Only
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
