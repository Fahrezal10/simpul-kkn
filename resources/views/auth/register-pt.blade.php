<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Institusi — SIMPUL-KKN</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon-indramayu.png') }}">

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
                    <h1 class="login-title">Registrasi Institusi<br>Perguruan Tinggi</h1>
                    <div class="login-brand-divider"></div>
                    <p class="login-subtitle">
                        Daftarkan kampus Anda untuk mengajukan permohonan KKN secara
                        digital. Pendaftaran akan diverifikasi oleh Bapperida Kabupaten Indramayu.
                    </p>
                </div>

                <div class="login-brand-footer">
                    <i class="bi bi-shield-check"></i>
                    <span>Sistem Terenkripsi &amp; Terintegrasi Satu Data Indramayu</span>
                </div>

            </div>
        </aside>

        {{-- Panel kanan: form registrasi --}}
        <main class="login-form">
            <div class="login-form-inner">
                <div class="mb-4">
                    <h2 class="login-heading">Daftarkan Institusi Anda</h2>
                    <p class="text-muted mb-0">Isi data institusi dan penanggung jawab. Setelah disetujui Bapperida, akun dapat digunakan untuk login.</p>
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

                <form method="POST" action="{{ route('register-pt.store') }}" enctype="multipart/form-data">
                    @csrf

                    <h6 class="form-section-title">Data Institusi</h6>
                    <div class="mb-3">
                        <label for="nama_pt" class="form-label">Nama Perguruan Tinggi <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-building"></i></span>
                            <input type="text" class="form-control" id="nama_pt" name="nama_pt"
                                   value="{{ old('nama_pt') }}" placeholder="mis. Universitas Indramayu" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="alamat" class="form-label">Alamat Institusi</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2"
                                  placeholder="Alamat kampus">{{ old('alamat') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="dokumen_legalitas" class="form-label">Dokumen Legalitas (opsional)</label>
                        <input type="file" class="form-control" id="dokumen_legalitas" name="dokumen_legalitas"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">PDF / JPG / PNG, maksimal 5 MB.</div>
                    </div>

                    <h6 class="form-section-title mt-4">Akun Login</h6>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email (digunakan untuk login) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="{{ old('email') }}" placeholder="nama@perguruan.ac.id" required>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Kata Sandi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password"
                                           placeholder="Minimal 8 karakter" required>
                                    <span class="input-group-text password-toggle" id="togglePassword" role="button">
                                        <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                    <input type="password" class="form-control" id="password_confirmation"
                                           name="password_confirmation" placeholder="Ulangi kata sandi" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="form-section-title mt-2">Penanggung Jawab (PIC)</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pic_nama" class="form-label">Nama PIC <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control" id="pic_nama" name="pic_nama"
                                           value="{{ old('pic_nama') }}" placeholder="Nama penanggung jawab" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pic_email" class="form-label">Email PIC <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="pic_email" name="pic_email"
                                           value="{{ old('pic_email') }}" placeholder="pic@perguruan.ac.id" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pic_telp" class="form-label">No. Telepon PIC</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                    <input type="text" class="form-control" id="pic_telp" name="pic_telp"
                                           value="{{ old('pic_telp') }}" placeholder="mis. 08xxxxxxxxxx">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-grid mt-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Daftarkan Institusi <i class="bi bi-box-arrow-in-right ms-1"></i>
                        </button>
                    </div>
                </form>

                <div class="divider my-4 d-flex align-items-center gap-2">
                    <span class="flex-grow-1 border-top"></span>
                    <span class="text-muted">Sudah terdaftar</span>
                    <span class="flex-grow-1 border-top"></span>
                </div>

                <div class="text-center">
                    <p class="mb-0">
                        <a href="{{ route('login') }}" class="fw-semibold">Kembali ke halaman masuk</a>
                    </p>
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
