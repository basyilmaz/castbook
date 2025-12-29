<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CastBook Kurulum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .install-container {
            max-width: 700px;
            margin: 40px auto;
        }
        .install-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .install-header {
            background: var(--primary-gradient);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .install-header h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        .install-body {
            padding: 30px;
        }
        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
        }
        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background: #e9ecef;
            color: #6c757d;
        }
        .step.active {
            background: var(--primary-gradient);
            color: white;
        }
        .step.completed {
            background: #28a745;
            color: white;
        }
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            padding: 12px 30px;
            font-weight: 600;
        }
        .btn-primary:hover {
            opacity: 0.9;
            background: var(--primary-gradient);
        }
        .requirement-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .requirement-item:last-child {
            border-bottom: none;
        }
        .status-ok {
            color: #28a745;
        }
        .status-fail {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="install-header">
                <h1><i class="bi bi-box-seam"></i> CastBook</h1>
                <p class="mb-0">Kurulum Sihirbazı</p>
            </div>
            
            <div class="install-body">
                <!-- Step Indicator -->
                <div class="step-indicator">
                    <div class="step {{ $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' }}">
                        @if($step > 1) <i class="bi bi-check"></i> @else 1 @endif
                    </div>
                    <div class="step {{ $step >= 2 ? ($step > 2 ? 'completed' : 'active') : '' }}">
                        @if($step > 2) <i class="bi bi-check"></i> @else 2 @endif
                    </div>
                    <div class="step {{ $step >= 3 ? ($step > 3 ? 'completed' : 'active') : '' }}">
                        @if($step > 3) <i class="bi bi-check"></i> @else 3 @endif
                    </div>
                    <div class="step {{ $step >= 4 ? ($step > 4 ? 'completed' : 'active') : '' }}">
                        @if($step > 4) <i class="bi bi-check"></i> @else 4 @endif
                    </div>
                    <div class="step {{ $step >= 5 ? ($step > 5 ? 'completed' : 'active') : '' }}">
                        @if($step > 5) <i class="bi bi-check"></i> @else 5 @endif
                    </div>
                    <div class="step {{ $step >= 6 ? 'active' : '' }}">6</div>
                </div>

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
        
        <p class="text-center text-muted mt-3">
            <small>CastBook v{{ config('app.version', '2.1.0') }} © {{ date('Y') }}</small>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
