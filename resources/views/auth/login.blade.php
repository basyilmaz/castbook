<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - {{ config('app.name', 'CastBook') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .login-card {
            border-radius: 16px;
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            padding: 2rem;
            text-align: center;
        }
        .login-header h3 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        .login-header p {
            color: rgba(255,255,255,0.8);
            margin: 0.5rem 0 0 0;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.15);
        }
        .btn-primary {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.4);
        }
    </style>
</head>
<body>
    <div class="min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card login-card shadow-lg border-0">
                        <div class="login-header">
                            <h3><i class="bi bi-book me-2"></i>{{ config('app.name', 'CastBook') }}</h3>
                            <p>Muhasebe Takip Sistemi</p>
                        </div>
                        <div class="card-body p-4">
                            @if(session('status'))
                                <div class="alert alert-success mb-3">
                                    {{ session('status') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.attempt') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-1"></i>E-posta Adresi
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="ornek@email.com" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock me-1"></i>Şifre
                                    </label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" placeholder="••••••••" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="remember" name="remember"
                                               {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">
                                            Beni hatırla
                                        </label>
                                    </div>
                                    @if(Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-decoration-none small">
                                            Şifremi unuttum
                                        </a>
                                    @endif
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
                                    </button>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center text-muted small bg-light py-3">
                            <i class="bi bi-shield-check me-1"></i>
                            © {{ date('Y') }} {{ config('app.name', 'CastBook') }} - Güvenli Giriş
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
</body>
</html>
