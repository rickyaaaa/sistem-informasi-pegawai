<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login &mdash; SIPNONA</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(rgba(30, 42, 59, 0.6), rgba(45, 63, 87, 0.6)), url('{{ asset('img/sipnona.png') }}') no-repeat center center fixed;
            background-size: 100% 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 300px;
            margin-top: 18vh; /* Pushed down to reveal center logo */
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
            overflow: hidden;
            background: transparent;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .login-header {
            background: linear-gradient(135deg, rgba(31, 41, 55, 0.55), rgba(153, 27, 27, 0.65));
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .login-header img {
            width: 42px;
            height: 42px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.3));
        }

        .login-header .header-text {
            text-align: center;
        }

        .login-header h1 {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .login-header p {
            font-size: 9px;
            color: rgba(255, 255, 255, .8);
            margin: 2px 0 0;
        }

        .login-body {
            background: rgba(255, 255, 255, 0.65);
            padding: 16px 20px 12px;
        }

        .input-group-custom {
            display: flex;
            border: 1px solid rgba(209, 213, 219, 0.7);
            border-radius: 8px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.85);
            transition: border-color .2s, box-shadow .2s;
        }

        .input-group-custom:focus-within {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, .15);
        }

        .input-group-custom .form-control {
            border: none;
            background: transparent;
            box-shadow: none;
            padding: 10px 14px;
            font-size: 14px;
            color: #4b5563;
            border-radius: 0;
        }
        
        .input-group-custom .form-control:focus {
            box-shadow: none;
            background: transparent;
        }

        .input-group-custom .input-group-text,
        .input-group-custom .btn {
            border: none;
            border-left: 1px solid rgba(209, 213, 219, 0.7);
            background: rgba(249, 250, 251, 0.7);
            color: #6b7280;
            border-radius: 0;
            padding: 0 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-group-custom .btn:hover {
            background: rgba(229, 231, 235, 0.8);
            color: #374151;
        }

        .btn-login {
            width: 100%;
            padding: 11px;
            font-weight: 600;
            font-size: 15px;
            border-radius: 8px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
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
            color: #dc2626;
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

        /* Mobile Responsiveness */
        @media (max-width: 576px) {
            .login-card {
                max-width: 90%;
                margin: 0 auto;
            }

            .login-body {
                padding: 24px;
            }

            .login-header {
                padding: 24px 20px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="login-card card">

        {{-- Header --}}
        <div class="login-header">
            <img src="{{ asset('img/polda.png') }}" alt="Logo Polda Lampung">
            <div class="header-text">
                <h1>SIPNONA</h1>
                <p>SISTEM INFORMASI PEGAWAI NON ASN</p>
            </div>
            <img src="{{ asset('img/sdm.png') }}" alt="Logo SDM Polri">
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
                <div class="mb-2">
                    <div class="input-group-custom">
                        <input type="text" id="username" name="username"
                            class="form-control @error('username') is-invalid @enderror" value="{{ old('username') }}"
                            placeholder="Username" required autofocus autocomplete="username">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-2">
                    <div class="input-group-custom">
                        <input type="password" id="password" name="password"
                            class="form-control @error('password') is-invalid @enderror" placeholder="Password" required
                            autocomplete="current-password">
                        <button class="btn" type="button" id="togglePassword">
                            <i class="bi bi-eye-slash-fill" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-4 mb-2">
                    <button type="submit" class="btn btn-login">
                        Masuk
                    </button>
                </div>
            </form>

            <p class="text-center text-muted mt-4 mb-0" style="font-size:12px;">
                &copy; 2026 By Universitas Teknokrat Indonesia
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            toggleIcon.className = type === 'password' ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    </script>
</body>

</html>