<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Rekon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background-color: #F3F4F6; }
        .sidebar { background-color: #EE2E24; min-height: 100vh; color: white; }
        .sidebar .nav-link { color: rgba(255,255,255,0.8); margin-bottom: 5px; border-radius: 10px; display: flex; align-items: center; padding: 0.6rem 0.9rem; }
        .sidebar .nav-link i { width: 1.25rem; text-align: center; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: white; color: #EE2E24; font-weight: bold; }
        .sidebar .nav-link:focus { box-shadow: none; }
        .sidebar-brand { font-size: 1.5rem; font-weight: bold; padding: 1.5rem 1rem; letter-spacing: 2px; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table-header { background-color: #E9ECEF; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }

        .breadcrumb { --bs-breadcrumb-divider: '>'; }
        .breadcrumb a { color: #EE2E24; text-decoration: none; font-weight: 600; }
        .breadcrumb a:hover { text-decoration: underline; }
        .breadcrumb .breadcrumb-item.active { color: #6C757D; font-weight: 600; }
        .breadcrumb .breadcrumb-item i { margin-right: 0.35rem; }
    </style>
</head>
<body>
<div class="container-fluid">
    <!-- Mobile Sidebar (Offcanvas) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar" aria-labelledby="mobileSidebarLabel">
        <div class="offcanvas-header" style="background-color:#EE2E24;color:white;">
            <h5 class="offcanvas-title" id="mobileSidebarLabel"><i class="fas fa-network-wired me-2"></i>REKON</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div class="sidebar p-3 d-flex flex-column" style="min-height:auto;">
                <ul class="nav flex-column flex-grow-1">
                    <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                    @if(auth()->user()?->canUpload())
                        <li class="nav-item"><a class="nav-link" href="{{ route('upload') }}"><i class="fas fa-upload me-2"></i> Upload Data</a></li>
                    @endif
                    <li class="nav-item"><a class="nav-link active" href="{{ route('admin.users') }}"><i class="fas fa-user-cog me-2"></i> Kelola User</a></li>
                    <li class="nav-item mt-auto pt-3">
                        <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Yakin ingin keluar?');">
                            @csrf
                            <button type="submit" class="nav-link w-100 text-start" style="border: none;">
                                <i class="fas fa-sign-out-alt me-2"></i> Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2 sidebar p-3 d-none d-md-flex flex-column">
            <div class="sidebar-brand mb-4">
                <i class="fas fa-network-wired"></i> REKON
            </div>
            <ul class="nav flex-column flex-grow-1">
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                @if(auth()->user()?->canUpload())
                    <li class="nav-item"><a class="nav-link" href="{{ route('upload') }}"><i class="fas fa-upload me-2"></i> Upload Data</a></li>
                @endif
                <li class="nav-item"><a class="nav-link active" href="{{ route('admin.users') }}"><i class="fas fa-user-cog me-2"></i> Kelola User</a></li>
                <li class="nav-item mt-auto pt-3">
                    <form method="POST" action="{{ route('logout') }}" onsubmit="return confirm('Yakin ingin keluar?');">
                        @csrf
                        <button type="submit" class="nav-link w-100 text-start" style="border: none;">
                            <i class="fas fa-sign-out-alt me-2"></i> Keluar
                        </button>
                    </form>
                </li>
            </ul>
        </div>

        <div class="col-12 col-md-10 p-4">
            <div class="d-flex d-md-none align-items-center justify-content-between mb-3">
                <button class="btn btn-outline-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="fw-bold">REKON</div>
                <div style="width:42px;"></div>
            </div>
            

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Kelola User</h4>
                <div class="text-muted small">Login sebagai: {{ auth()->user()->name }} ({{ auth()->user()->username }})</div>
            </div>

            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Daftar User</h6>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateUser">
                        <i class="fas fa-plus me-2"></i>Buat Akun
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-header">
                            <tr>
                                <th>NAMA</th>
                                <th>USERNAME</th>
                                <th>EMAIL</th>
                                <th>ROLE</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($users as $u)
                            <tr>
                                <td>{{ $u->name }}</td>
                                <td>{{ $u->username }}</td>
                                <td>{{ $u->email }}</td>
                                <td>
                                    @if($u->role === 'super_admin')
                                        <span class="badge bg-danger">SUPER ADMIN</span>
                                    @elseif($u->role === 'admin')
                                        <span class="badge bg-primary">ADMIN</span>
                                    @else
                                        <span class="badge bg-secondary">USER</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-primary me-2"
                                        title="Edit"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEditUser"
                                        data-user-id="{{ $u->id }}"
                                        data-user-name="{{ e($u->name) }}"
                                        data-user-username="{{ e($u->username) }}"
                                        data-user-email="{{ e($u->email) }}"
                                        data-user-role="{{ e($u->role) }}"
                                    >
                                        <i class="fas fa-pen"></i>
                                    </button>

                                    <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" class="d-inline" onsubmit="return confirm('Hapus user ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus" @disabled(auth()->id() === $u->id)>
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal Create User -->
            <div class="modal fade" id="modalCreateUser" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 15px; border: none;">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i>Buat Akun User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('admin.users.store') }}" autocomplete="off">
                            @csrf
                            <div class="modal-body">
                                <!-- Dummy fields to reduce browser autofill -->
                                <input type="text" name="fake_username" autocomplete="username" style="display:none">
                                <input type="password" name="fake_password" autocomplete="new-password" style="display:none">

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama</label>
                                        <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" autocomplete="off">
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" id="create_username" value="{{ $errors->any() ? old('username') : '' }}" class="form-control @error('username') is-invalid @enderror" placeholder="maks 10 karakter" autocomplete="off" autocapitalize="none" autocorrect="off" spellcheck="false" maxlength="10" pattern="[A-Za-z0-9_-]{1,10}" title="Maksimal 10 karakter. Hanya huruf/angka/underscore/dash.">
                                        @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" autocomplete="off">
                                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" id="create_password" value="" class="form-control @error('password') is-invalid @enderror" placeholder="min 8 karakter, ada huruf besar, tanpa karakter spesial" autocomplete="new-password" minlength="8" pattern="(?=.*[A-Z])[A-Za-z0-9]{8,}" title="Minimal 8 karakter, harus ada huruf besar, dan hanya boleh huruf/angka.">
                                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="mt-3">
                                    <label class="form-label">Ulangi Password</label>
                                    <input type="password" name="password_confirmation" id="create_password_confirmation" value="" class="form-control" placeholder="ulang password" autocomplete="new-password" minlength="8" pattern="(?=.*[A-Z])[A-Za-z0-9]{8,}" title="Harus sama dengan password.">
                                </div>

                                <div class="mt-3">
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                                        <option value="admin" @selected(old('role') === 'admin')>ADMIN</option>
                                        <option value="user" @selected(old('role') === 'user')>USER</option>
                                        <option value="super_admin" @selected(old('role') === 'super_admin')>SUPER ADMIN</option>
                                    </select>
                                    @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    <div class="text-muted small mt-1">USER = hanya lihat dashboard & hasil rekon.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-2"></i>Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal Edit User -->
            <div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content" style="border-radius: 15px; border: none;">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-user-edit me-2"></i>Edit User</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" id="formEditUser" action="">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nama</label>
                                        <input type="text" name="name" id="edit_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" name="username" id="edit_username" class="form-control" required maxlength="10" pattern="[A-Za-z0-9_-]{1,10}" title="Maksimal 10 karakter. Hanya huruf/angka/underscore/dash.">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" id="edit_email" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role" id="edit_role" class="form-select" required>
                                            <option value="admin">ADMIN</option>
                                            <option value="user">USER</option>
                                            <option value="super_admin">SUPER ADMIN</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label">Password Baru (opsional)</label>
                                    <input type="password" name="password" id="edit_password" class="form-control" placeholder="Min 8 karakter, ada huruf besar, tanpa karakter spesial (kosongkan jika tidak diubah)" minlength="8" pattern="(?=.*[A-Z])[A-Za-z0-9]{8,}" title="Minimal 8 karakter, harus ada huruf besar, dan hanya boleh huruf/angka.">
                                </div>

                                <div class="mt-3">
                                    <label class="form-label">Ulangi Password Baru (opsional)</label>
                                    <input type="password" name="password_confirmation" id="edit_password_confirmation" class="form-control" placeholder="Ulangi password baru (kosongkan jika tidak diubah)" minlength="8" pattern="(?=.*[A-Z])[A-Za-z0-9]{8,}" title="Harus sama dengan password baru.">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-check me-2"></i>Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    var modalEdit = document.getElementById('modalEditUser');
                    if (modalEdit) {
                        modalEdit.addEventListener('show.bs.modal', function (event) {
                            var button = event.relatedTarget;
                            if (!button) return;

                            var id = button.getAttribute('data-user-id');
                            var name = button.getAttribute('data-user-name') || '';
                            var username = button.getAttribute('data-user-username') || '';
                            var email = button.getAttribute('data-user-email') || '';
                            var role = button.getAttribute('data-user-role') || 'user';

                            document.getElementById('edit_name').value = name;
                            document.getElementById('edit_username').value = username;
                            document.getElementById('edit_email').value = email;
                            document.getElementById('edit_role').value = role;
                            document.getElementById('edit_password').value = '';
                            var epc = document.getElementById('edit_password_confirmation');
                            if (epc) epc.value = '';

                            document.getElementById('formEditUser').action = "{{ url('/users') }}/" + id;
                        });
                    }

                    // Jika validasi create gagal, buka modal create otomatis.
                    var hasCreateErrors = {{ $errors->has('name') || $errors->has('username') || $errors->has('email') || $errors->has('password') || $errors->has('role') ? 'true' : 'false' }};
                    if (hasCreateErrors) {
                        var modal = new bootstrap.Modal(document.getElementById('modalCreateUser'));
                        modal.show();
                    }

                    // Saat modal create dibuka normal (tanpa error), kosongkan username/password
                    var modalCreateEl = document.getElementById('modalCreateUser');
                    if (modalCreateEl) {
                        modalCreateEl.addEventListener('show.bs.modal', function () {
                            if (hasCreateErrors) return;

                            var u = document.getElementById('create_username');
                            var p = document.getElementById('create_password');
                            var pc = document.getElementById('create_password_confirmation');
                            if (u) u.value = '';
                            if (p) p.value = '';
                            if (pc) pc.value = '';
                        });
                    }
                });
            </script>

        </div>
    </div>
</div>
</body>
</html>
