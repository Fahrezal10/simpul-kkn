<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMPUL-KKN') — SIMPUL-KKN</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet"
          integrity="sha384-XGjxtQfXaH2tnPFa9x+ruJTuLE3Aa6LhHSWRr1XeTyhezb4abCG4ccI5AkVDxqC+" crossorigin="anonymous">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body class="app-body">
    <div class="app-shell">
        {{-- Sidebar --}}
        <aside class="sidebar" id="appSidebar">
            <div class="sidebar-brand">
                <i class="bi bi-bezier2"></i>
                <span class="brand-text">SIMPUL-KKN</span>
                <button class="btn btn-link d-lg-none sidebar-close" id="sidebarClose" aria-label="Tutup menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <ul class="sidebar-nav">
                <li class="nav-section">Menu Utama</li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                <span class="role-badge">{{ strtoupper($roleLabel ?? '') }}</span>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm sidebar-logout">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </aside>
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        {{-- Konten --}}
        <div class="app-main">
            <nav class="navbar app-navbar sticky-top">
                <div class="container-fluid px-3 px-lg-4">
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-link navbar-toggler-btn d-lg-none" id="sidebarToggle" aria-label="Buka menu">
                            <i class="bi bi-list"></i>
                        </button>
                        <button class="btn btn-link navbar-toggler-btn d-none d-lg-inline-flex sidebar-collapse-btn"
                                id="sidebarCollapse" aria-label="Ciutkan sidebar" title="Ciutkan menu">
                            <i class="bi bi-layout-sidebar"></i>
                        </button>
                        <span class="navbar-brand d-none d-lg-inline">Sistem Informasi Manajemen KKN</span>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                        <button class="btn btn-link position-relative notification-btn" title="Notifikasi" data-bs-toggle="tooltip">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-amber">0</span>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-link user-chip dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="user-avatar">{{ strtoupper(substr($user->nama ?? '?', 0, 1)) }}</span>
                                <span class="user-name d-none d-md-inline">{{ $user->nama ?? '' }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text small text-muted">{{ $user->email ?? '' }}</span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="bi bi-box-arrow-right me-1"></i> Keluar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <main class="app-content container-fluid px-3 px-lg-4 py-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="app-footer">
                &copy; {{ date('Y') }} Bapperida Kabupaten Indramayu — SIMPUL-KKN
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
