<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMPUL-KKN') — SIMPUL-KKN</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-indramayu.png') }}">

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

                @php $role = optional(Auth::user()->role)->nama_role; @endphp

                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard.gis*') ? 'active' : '' }}" href="{{ route('dashboard.gis') }}">
                        <i class="bi bi-map"></i>
                        <span>Peta (GIS)</span>
                    </a>
                </li>

                @if ($role === 'perguruan_tinggi' || $role === 'superadmin')
                    <li class="nav-section">Perguruan Tinggi</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('perguruan-tinggi.permohonan.*') ? 'active' : '' }}" href="{{ route('perguruan-tinggi.permohonan.index') }}">
                            <i class="bi bi-journal-text"></i>
                            <span>Permohonan KKN</span>
                        </a>
                    </li>
                @endif

                @if ($role === 'bapperida' || $role === 'superadmin')
                    <li class="nav-section">Bapperida</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('bapperida.pt.*') ? 'active' : '' }}" href="{{ route('bapperida.pt.index') }}">
                            <i class="bi bi-building-check"></i>
                            <span>Persetujuan PT</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('bapperida.permohonan.*') ? 'active' : '' }}" href="{{ route('bapperida.permohonan.index') }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Verifikasi Permohonan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('bapperida.matching.*') ? 'active' : '' }}" href="{{ route('bapperida.matching.index') }}">
                            <i class="bi bi-magic"></i>
                            <span>Matching KKN</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('bapperida.desa.*') ? 'active' : '' }}" href="{{ route('bapperida.desa.index') }}">
                            <i class="bi bi-geo-alt"></i>
                            <span>Master Data Desa</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('bapperida.approval-final.*') ? 'active' : '' }}" href="{{ route('bapperida.approval-final.index') }}">
                            <i class="bi bi-check2-circle"></i>
                            <span>Persetujuan Akhir</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('bapperida.monitoring.*') ? 'active' : '' }}" href="{{ route('bapperida.monitoring.index') }}">
                            <i class="bi bi-graph-up"></i>
                            <span>Monitoring & Evaluasi</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('master-data.*') ? 'active' : '' }}" href="{{ route('master-data.index') }}">
                            <i class="bi bi-database-gear"></i>
                            <span>Master Data</span>
                        </a>
                    </li>
                @endif

                @if ($role === 'kecamatan' || $role === 'superadmin')
                    <li class="nav-section">Kecamatan</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('kecamatan.verifikasi.*') ? 'active' : '' }}" href="{{ route('kecamatan.verifikasi.index') }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Verifikasi Kesiapan Desa</span>
                        </a>
                    </li>
                @endif

                @if ($role === 'desa' || $role === 'superadmin')
                    <li class="nav-section">Desa</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('desa.profil.*') ? 'active' : '' }}" href="{{ route('desa.profil.index') }}">
                            <i class="bi bi-house-gear"></i>
                            <span>Profil & Potensi Desa</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('desa.evaluasi.*') ? 'active' : '' }}" href="{{ route('desa.evaluasi.index') }}">
                            <i class="bi bi-star"></i>
                            <span>Evaluasi Kelompok</span>
                        </a>
                    </li>
                @endif

                @if ($role === 'perangkat_daerah' || $role === 'superadmin')
                    <li class="nav-section">Perangkat Daerah</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('perangkat-daerah.isu-strategis.*') ? 'active' : '' }}" href="{{ route('perangkat-daerah.isu-strategis.index') }}">
                            <i class="bi bi-bullseye"></i>
                            <span>Isu Strategis</span>
                        </a>
                    </li>
                @endif

                @if ($role === 'mahasiswa' || $role === 'superadmin')
                    <li class="nav-section">Mahasiswa</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('mahasiswa.logbook.*') ? 'active' : '' }}" href="{{ route('mahasiswa.logbook.index') }}">
                            <i class="bi bi-journal-text"></i>
                            <span>Logbook Harian</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('mahasiswa.laporan-akhir.*') ? 'active' : '' }}" href="{{ route('mahasiswa.laporan-akhir.index') }}">
                            <i class="bi bi-file-earmark-richtext"></i>
                            <span>Laporan Akhir</span>
                        </a>
                    </li>
                @endif

                @if ($role === 'dosen' || $role === 'superadmin')
                    <li class="nav-section">DPL</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dosen.logbook.*') ? 'active' : '' }}" href="{{ route('dosen.logbook.index') }}">
                            <i class="bi bi-clipboard-check"></i>
                            <span>Approval Logbook</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dosen.laporan-akhir.*') ? 'active' : '' }}" href="{{ route('dosen.laporan-akhir.index') }}">
                            <i class="bi bi-file-earmark-check"></i>
                            <span>Verifikasi Laporan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dosen.evaluasi.*') ? 'active' : '' }}" href="{{ route('dosen.evaluasi.index') }}">
                            <i class="bi bi-star"></i>
                            <span>Evaluasi Kelompok</span>
                        </a>
                    </li>
                @endif
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
                        {{-- SYS-01: Dropdown popup notifikasi in-app --}}
                        @php
                            $recentNotifs = Auth::user()->notifications()->limit(5)->get();
                            $unreadCount  = Auth::user()->unreadNotifications->count();
                        @endphp
                        <div class="dropdown notification-dropdown" id="notificationDropdown">
                            <button class="btn btn-link position-relative notification-btn" data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside" aria-expanded="false" title="Notifikasi">
                                <i class="bi bi-bell"></i>
                                @if ($unreadCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-amber" id="notifCount">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </button>
                            <div class="dropdown-menu dropdown-menu-end notification-panel">
                                <div class="notification-panel-header">
                                    <span class="fw-semibold">Notifikasi</span>
                                    @if ($recentNotifs->isNotEmpty())
                                        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-link btn-sm p-0 text-decoration-none text-primary">
                                                Tandai semua dibaca
                                            </button>
                                        </form>
                                    @endif
                                </div>
                                <div class="notification-panel-body">
                                    @forelse ($recentNotifs as $notification)
                                        @php
                                            $data = $notification->data;
                                            $isUnread = is_null($notification->read_at);
                                            $notifUrl = $data['url'] ?? route('notifications.index');
                                        @endphp
                                        <a href="{{ $notifUrl }}"
                                           class="dropdown-item notification-list-item {{ $isUnread ? 'notification-list-unread' : '' }}"
                                           data-notif-id="{{ $notification->id }}">
                                            <div class="d-flex align-items-start gap-2">
                                                <i class="bi {{ ($data['status'] ?? '') === 'ditolak' ? 'bi-x-circle text-danger' : 'bi-info-circle text-primary' }} notification-list-icon"></i>
                                                <div>
                                                    <div class="notification-list-msg">{{ $data['message'] ?? 'Notifikasi' }}</div>
                                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="notification-list-empty text-muted">
                                            <i class="bi bi-bell-slash"></i>
                                            <div>Belum ada notifikasi</div>
                                        </div>
                                    @endforelse
                                </div>
                                <a href="{{ route('notifications.index') }}" class="notification-panel-footer">
                                    Lihat semua notifikasi
                                </a>
                            </div>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-link user-chip dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                @php
                                    // Inisial user: ambil huruf pertama dari maks. 2 kata pertama nama.
                                    $initials = collect(preg_split('/\s+/', trim(Auth::user()->nama ?? '')))
                                        ->filter()
                                        ->take(2)
                                        ->map(fn ($w) => strtoupper(mb_substr($w, 0, 1)))
                                        ->implode('');
                                    $initials = $initials ?: '?';
                                @endphp
                                <span class="user-avatar">{{ $initials }}</span>
                                <span class="user-name d-none d-md-inline">{{ Auth::user()->nama ?? '' }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><span class="dropdown-item-text small text-muted">{{ Auth::user()->email ?? '' }}</span></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a href="{{ route('notifications.index') }}" class="dropdown-item">
                                        <i class="bi bi-bell me-1"></i> Notifikasi
                                    </a>
                                </li>
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
            integrity="sha384-1H217gwSVyLSIfaLxHbE7dRb3v4mYCKbpQvzx0cegeju1MVsGrX5xXxAvs/HgeFs"
            crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
