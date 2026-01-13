<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Rekon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background-color: #F3F4F6; }
        .brand { color: #EE2E24; font-weight: 800; letter-spacing: 2px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .btn-primary { background-color: #EE2E24; border-color: #EE2E24; }
        .btn-primary:hover { background-color: #d6271f; border-color: #d6271f; }

        .password-wrap { position: relative; }
        .password-wrap .password-input { padding-right: 2.75rem; }
        .password-wrap .password-toggle {
            position: absolute;
            top: 50%;
            right: 0.6rem;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            padding: 0.25rem;
            line-height: 1;
            color: #6C757D;
        }
        .password-wrap .password-toggle:focus { outline: none; box-shadow: none; }

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
        <div class="col-md-5">
            <div class="text-center mb-3">
                <div class="brand"><i class="fas fa-network-wired me-2"></i>SIRMA</div>
                <div class="text-muted small">Silakan login untuk melanjutkan</div>
            </div>

            <div class="card p-4">
                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" class="form-control @error('username') is-invalid @enderror" autocomplete="username" autocapitalize="none" spellcheck="false" autofocus maxlength="10" pattern="[A-Za-z0-9_-]{1,10}" title="Maksimal 10 karakter. Hanya huruf/angka/underscore/dash.">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="password-wrap">
                            <input id="password" type="password" name="password" class="form-control password-input @error('password') is-invalid @enderror" aria-label="Password" autocomplete="current-password">
                            <button class="password-toggle d-none" type="button" id="togglePassword" aria-label="Tampilkan/Sembunyikan Password">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </button>
                </form>

                @if (\App\Models\User::query()->count() === 0)
                    <hr class="my-4">
                    <div class="alert alert-warning mb-0">
                        Belum ada akun di database. Buka <a href="{{ route('setup') }}" class="fw-bold">Setup Super Admin</a> untuk membuat akun pertama.
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
    (function () {
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');
        const toggleIcon = document.getElementById('togglePasswordIcon');

        function stripSpaces(el) {
            if (!el) return;
            const next = (el.value || '').replace(/\s+/g, '');
            if (next !== el.value) el.value = next;
        }

        if (usernameInput) {
            usernameInput.addEventListener('input', function () { stripSpaces(usernameInput); });
            usernameInput.addEventListener('paste', function () {
                setTimeout(function () { stripSpaces(usernameInput); }, 0);
            });
        }

        if (!passwordInput || !toggleButton || !toggleIcon) return;

        function syncToggleVisibility() {
            const hasValue = (passwordInput.value || '').length > 0;
            toggleButton.classList.toggle('d-none', !hasValue);
        }

        passwordInput.addEventListener('input', syncToggleVisibility);
        // Handle browser autofill
        setTimeout(syncToggleVisibility, 0);

        toggleButton.addEventListener('click', function () {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            toggleIcon.classList.toggle('fa-eye', !isPassword);
            toggleIcon.classList.toggle('fa-eye-slash', isPassword);
        });
    })();
</script>

</body>
</html>
