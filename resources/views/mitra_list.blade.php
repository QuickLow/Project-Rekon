<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mitra - Rekon</title>
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
                    @if(auth()->user()?->isSuperAdmin())
                        <li class="nav-item"><a class="nav-link" href="{{ route('admin.users') }}"><i class="fas fa-user-cog me-2"></i> Kelola User</a></li>
                    @endif
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
                @if(auth()->user()?->isSuperAdmin())
                    <li class="nav-item"><a class="nav-link" href="{{ route('admin.users') }}"><i class="fas fa-user-cog me-2"></i> Kelola User</a></li>
                @endif
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
            <!-- Mobile top bar -->
            <div class="d-md-none d-flex align-items-center justify-content-between mb-3">
                <button class="btn btn-outline-secondary" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="fw-bold" style="letter-spacing:2px;">REKON</div>
                <div style="width: 42px;"></div>
            </div>
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-users"></i>Daftar Mitra</li>
                </ol>
            </nav>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Daftar Mitra</h4>
            </div>
            <div class="card p-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-header">
                            <tr>
                                <th>MITRA</th>
                                <th class="text-center">JUMLAH LOP</th>
                                <th class="text-center">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($mitras as $m)
                            <tr>
                                <td>{{ $m->mitra_name }}</td>
                                <td class="text-center">{{ $m->lop_count }}</td>
                                <td class="text-center">
                                    <a href="{{ route('rekon.mitra', [$project->id, $m->mitra_name]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-list me-1"></i> Lihat LOP
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>