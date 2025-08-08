<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Absensi QR Code')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .qr-scanner-container {
            max-width: 500px;
            margin: 0 auto;
        }
        .alert-custom {
            border-radius: 10px;
            font-weight: 500;
        }
        .card-custom {
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-custom {
            border-radius: 25px;
            padding: 10px 30px;
            font-weight: 600;
        }
        .scanner-result {
            background: #ffffff;
            color: #212529;
            border: 2px solid #28a745;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 20px rgba(40, 167, 69, 0.15);
        }
        .status-badge {
            font-size: 0.9em;
            padding: 5px 15px;
            border-radius: 20px;
        }
        .status-hadir { background-color: #28a745; }
        .status-telat { background-color: #ffc107; color: #212529; }
        .status-alpha { background-color: #dc3545; }
    </style>
    @stack('styles')
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="{{ route('absensi.index') }}">
                <i class="fas fa-qrcode me-2"></i>
                Sistem Absensi QR
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('absensi.index') }}">
                            <i class="fas fa-camera me-1"></i> Scan QR
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('jadwal-kelas.index') }}">
                            <i class="fas fa-calendar-alt me-1"></i> Jadwal Persesi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('absensi.laporan') }}">
                            <i class="fas fa-chart-bar me-1"></i> Laporan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('qr.index') }}">
                            <i class="fas fa-qrcode me-1"></i> QR Siswa
                        </a>
                    </li>
                    
                    @auth
                        @if(auth()->user()->role === 'admin')
                            <!-- Admin Only Links -->
                            <li class="nav-item">
                                <a class="nav-link text-danger" href="{{ route('admin.generate-qr') }}">
                                    <i class="fas fa-shield-alt me-1"></i> Admin QR
                                </a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle text-warning" href="#" id="adminDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fas fa-shield-alt me-1"></i> Admin
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item text-danger" href="{{ route('admin.generate-qr') }}">
                                            <i class="fas fa-qrcode me-2"></i> Generate QR Admin
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-primary" href="{{ route('admin.dashboard') }}">
                                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <small class="dropdown-item-text text-muted">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Akses penuh administrator
                                        </small>
                                    </li>
                                </ul>
                            </li>
                        @endif
                        
                        <!-- User Info -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-light" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i> {{ auth()->user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('guru.dashboard') }}">
                                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('qr.login.logout') }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <!-- Login Link -->
                        <li class="nav-item">
                            <a class="nav-link text-success" href="{{ route('qr.login.form') }}">
                                <i class="fas fa-sign-in-alt me-1"></i> Login Staff
                            </a>
                        </li>
                    @endauth
                </ul>
                <!-- Real-time Clock -->
                <div class="navbar-text text-white me-3">
                    <i class="fas fa-clock me-1"></i>
                    <span id="realtime-clock">Loading...</span>
                </div>
                <div class="navbar-text text-white small">
                    <span id="realtime-date">Loading...</span>
                </div>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Setup AJAX CSRF Token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Real-time Clock
        function updateClock() {
            const now = new Date();
            const options = {
                timeZone: 'Asia/Jakarta',
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const dateOptions = {
                timeZone: 'Asia/Jakarta',
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            };
            
            const timeString = now.toLocaleTimeString('id-ID', options);
            const dateString = now.toLocaleDateString('id-ID', dateOptions);
            
            $('#realtime-clock').text(timeString);
            $('#realtime-date').text(dateString);
        }
        
        // Update clock immediately and then every second
        $(document).ready(function() {
            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
    @stack('scripts')
</body>
</html>
