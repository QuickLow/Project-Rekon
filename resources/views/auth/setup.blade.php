<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Super Admin - Rekon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background-color: #F3F4F6; }
        .brand { color: #EE2E24; font-weight: 800; letter-spacing: 2px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-primary { background-color: #EE2E24; border-color: #EE2E24; }
        .btn-primary:hover { background-color: #d6271f; border-color: #d6271f; }

        .password-wrap { position: relative; }
        .password-input { padding-right: 2.75rem; }
        .password-toggle {
            position: absolute;
            right: 0.5rem;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;
            border: 0;
            background: transparent;
            color: #6C757D;
            padding: 0.25rem 0.5rem;
            line-height: 1;
        }
        .password-toggle:focus { outline: none; box-shadow: none; }

        /* Hide native password reveal (Edge/IE) so it doesn't duplicate our eye icon */
        input[type="password"]::-ms-reveal,
        input[type="password"]::-ms-clear {
            display: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-7 col-lg-6">
            <div class="text-center mb-3">
                <div class="brand"><i class="fas fa-user-shield me-2"></i>SETUP SUPER ADMIN</div>
                <div class="text-muted small">Hanya muncul jika belum ada user</div>
            </div>

            <div class="card p-4">
                <form method="POST" action="{{ route('setup.post') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" maxlength="10" pattern="[A-Za-z0-9_-]{1,10}" title="Maksimal 10 karakter. Hanya huruf/angka/underscore/dash.">
                            @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Dipakai untuk login (maksimal 10 karakter).</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password</label>
                            <div class="password-wrap">
                                <input type="password" name="password" id="setup_password" class="form-control password-input @error('password') is-invalid @enderror" minlength="8" pattern="(?=.*[A-Z])[A-Za-z0-9]{8,}" title="Minimal 8 karakter, harus ada huruf besar, dan hanya boleh huruf/angka." autocomplete="new-password">
                                <button class="password-toggle" type="button" id="toggleSetupPassword" aria-label="Tampilkan/Sembunyikan Password">
                                    <i class="fas fa-eye" id="toggleSetupPasswordIcon"></i>
                                </button>
                            </div>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Minimal 8 karakter, wajib ada huruf besar, tanpa karakter spesial.</div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-check me-2"></i>Buat Super Admin
                    </button>
                </form>

                <div class="mt-3 text-center">
                    <a href="{{ route('login') }}" class="text-muted">Kembali ke login</a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    (function () {
        function wirePasswordToggle(inputId, buttonId, iconId) {
            var input = document.getElementById(inputId);
            var button = document.getElementById(buttonId);
            var icon = document.getElementById(iconId);
            if (!input || !button || !icon) return;

            // Pastikan icon sesuai tipe input saat load.
            if (input.type === 'text') {
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }

            button.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                icon.classList.toggle('fa-eye', !show);
                icon.classList.toggle('fa-eye-slash', show);
                input.focus();
            });
        }

        wirePasswordToggle('setup_password', 'toggleSetupPassword', 'toggleSetupPasswordIcon');
    })();
</script>

</body>
</html>
