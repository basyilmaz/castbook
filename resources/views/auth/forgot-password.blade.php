<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Şifremi Unuttum - {{ config('app.name', 'CastBook') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .auth-card {
            border-radius: 16px;
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            padding: 2rem;
            text-align: center;
        }
        .auth-header h3 {
            color: white;
            margin: 0;
            font-weight: 600;
        }
        .auth-header p {
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
                    <div class="card auth-card shadow-lg border-0">
                        <div class="auth-header">
                            <h3><i class="bi bi-key me-2"></i>Şifre Sıfırlama</h3>
                            <p>E-posta adresinize sıfırlama linki göndereceğiz</p>
                        </div>
                        <div class="card-body p-4">
                            @if(session('status'))
                                <div class="alert alert-success mb-3">
                                    <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger mb-3">
                                    <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf
                                <div class="mb-4">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-1"></i>E-posta Adresi
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" value="{{ old('email') }}" 
                                           placeholder="ornek@email.com" required autofocus>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Kayıtlı e-posta adresinizi girin
                                    </div>
                                </div>
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-send me-2"></i>Sıfırlama Linki Gönder
                                    </button>
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('login') }}" class="text-decoration-none">
                                        <i class="bi bi-arrow-left me-1"></i>Giriş sayfasına dön
                                    </a>
                                </div>
                            </form>
                        </div>
                        <div class="card-footer text-center text-muted small bg-light py-3">
                            <i class="bi bi-shield-check me-1"></i>
                            © {{ date('Y') }} {{ config('app.name', 'CastBook') }}
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
