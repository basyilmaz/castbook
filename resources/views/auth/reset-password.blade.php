<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Şifre Oluştur - {{ config('app.name', 'CastBook') }}</title>
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
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 0.5rem;
            transition: all 0.3s ease;
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
                            <h3><i class="bi bi-shield-lock me-2"></i>Yeni Şifre Oluştur</h3>
                            <p>Güvenli bir şifre belirleyin</p>
                        </div>
                        <div class="card-body p-4">
                            @if($errors->any())
                                <div class="alert alert-danger mb-3">
                                    <i class="bi bi-exclamation-circle me-2"></i>
                                    @foreach($errors->all() as $error)
                                        {{ $error }}<br>
                                    @endforeach
                                </div>
                            @endif

                            <form method="POST" action="{{ route('password.update') }}">
                                @csrf
                                <input type="hidden" name="token" value="{{ $token }}">
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope me-1"></i>E-posta Adresi
                                    </label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                           id="email" name="email" 
                                           value="{{ $email ?? old('email') }}" 
                                           required readonly>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock me-1"></i>Yeni Şifre
                                    </label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                           id="password" name="password" 
                                           placeholder="En az 8 karakter" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="password-strength bg-secondary" id="passwordStrength"></div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password_confirmation" class="form-label">
                                        <i class="bi bi-lock-fill me-1"></i>Şifre Tekrar
                                    </label>
                                    <input type="password" class="form-control"
                                           id="password_confirmation" name="password_confirmation" 
                                           placeholder="Şifrenizi tekrar girin" required>
                                </div>
                                
                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-check-lg me-2"></i>Şifreyi Güncelle
                                    </button>
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
    <script>
        // Şifre güçlülük göstergesi
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;
            
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            const colors = ['#dc3545', '#fd7e14', '#ffc107', '#198754', '#0d6efd'];
            const widths = ['20%', '40%', '60%', '80%', '100%'];
            
            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthBar.style.backgroundColor = '#6c757d';
            } else {
                strengthBar.style.width = widths[strength - 1] || '20%';
                strengthBar.style.backgroundColor = colors[strength - 1] || '#dc3545';
            }
        });
    </script>
</body>
</html>
