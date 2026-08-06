<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — SIMPUL-KKN</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="login-body">
    <div class="login-split">

        {{-- Panel kiri: informasi sistem --}}
        <aside class="login-brand" style="--login-brand-image: url('{{ asset('images/bappeda-office.jpg') }}');">
            <div class="login-brand-inner">

                <div class="login-brand-header">
                    <div class="login-logo"><i class="bi bi-bezier2"></i></div>
                    <div>
                        <div class="login-brand-name">SIMPUL-KKN</div>
                        <div class="login-brand-org">BAPPERIDA INDRAMAYU</div>
                    </div>
                </div>

                <div class="login-brand-body">
                    <h1 class="login-title">Sistem Informasi Manajemen<br>&amp; Pemantauan Kuliah Kerja Nyata</h1>
                    <div class="login-brand-divider"></div>
                    <p class="login-subtitle">
                        Platform integrasi data pengabdian masyarakat untuk pembangunan
                        daerah Kabupaten Indramayu yang lebih terukur dan berdampak nyata.
                    </p>
                </div>

                <div class="login-brand-footer">
                    <i class="bi bi-shield-check"></i>
                    <span>Sistem Terenkripsi &amp; Terintegrasi Satu Data Indramayu</span>
                </div>

            </div>
        </aside>

        {{-- Panel kanan: form login --}}
        <main class="login-form">
            <div class="login-form-inner">
                <div class="mb-4">
                    <h2 class="login-heading">Selamat Datang</h2>
                    <p class="text-muted mb-0">Silakan masuk dengan akun resmi Anda untuk mengakses dashboard manajemen SIMPUL-KKN.</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Username atau Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="text" class="form-control" id="email" name="email"
                                   value="{{ old('email') }}" placeholder="nama@perguruan.ac.id"
                                   required autofocus autocomplete="username">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                            <span class="input-group-text password-toggle" id="togglePassword" role="button">
                                <i class="bi bi-eye" id="togglePasswordIcon"></i>
                            </span>
                        </div>
                        <div class="mt-2 d-flex justify-content-between align-items-center">
                            <label for="remember" class="form-check-label small mb-0">
                                <input class="form-check-input me-1" type="checkbox" id="remember" name="remember">
                                Ingat saya
                            </label>
                            <a href="#" class="small">Lupa password?</a>
                        </div>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Masuk ke Dashboard <i class="bi bi-box-arrow-in-right ms-1"></i>
                        </button>
                    </div>
                </form>

                <div class="divider my-4 d-flex align-items-center gap-2">
                    <span class="flex-grow-1 border-top"></span>
                    <span class="text-muted">Perguruan tinggi baru</span>
                    <span class="flex-grow-1 border-top"></span>
                </div>

                <div class="text-center">
                    <p class="text-muted small mb-2">Kampus Anda belum terdaftar di SIMPUL-KKN?</p>
                    <p class="mb-0">
                        <a href="#" class="fw-semibold">Daftarkan Institusi Anda</a>
                    </p>
                    <p class="text-muted small mt-2 mb-0">Pendaftaran akan diverifikasi oleh Bapperida.</p>
                </div>
            </div>
        </main>

    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        togglePassword?.addEventListener('click', function () {
            const isHidden = passwordInput.type === 'password';
            passwordInput.type = isHidden ? 'text' : 'password';
            toggleIcon.classList.toggle('bi-eye');
            toggleIcon.classList.toggle('bi-eye-slash');
        });
    </script>
</body>
</html>