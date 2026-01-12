<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekon Telkom Akses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #F3F4F6; }
        
        /* Sidebar Merah Telkom */
        .sidebar {
            background-color: #EE2E24; /* Merah Telkom */
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            margin-bottom: 5px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            padding: 0.6rem 0.9rem;
        }
        .sidebar .nav-link i { width: 1.25rem; text-align: center; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: white;
            color: #EE2E24;
            font-weight: bold;
        }
        .sidebar .nav-link:focus { box-shadow: none; }
        .sidebar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            padding: 1.5rem 1rem;
            letter-spacing: 2px;
        }

        /* Card & Table */
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .table-header { background-color: #E9ECEF; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; }
        .badge-match { background-color: #10B981; color: white; }
        .badge-mismatch { background-color: #EE2E24; color: white; }
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
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    @if(auth()->user()?->canUpload())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('upload') }}">
                                <i class="fas fa-upload me-2"></i> Upload Data
                            </a>
                        </li>
                    @endif
                    @if(auth()->user()?->isSuperAdmin())
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.users') }}">
                                <i class="fas fa-user-cog me-2"></i> Kelola User
                            </a>
                        </li>
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
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                @if(auth()->user()?->canUpload())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('upload') }}">
                            <i class="fas fa-upload me-2"></i> Upload Data
                        </a>
                    </li>
                @endif
                @if(auth()->user()?->isSuperAdmin())
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users') }}">
                            <i class="fas fa-user-cog me-2"></i> Kelola User
                        </a>
                    </li>
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

            {{-- ALERT --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Dashboard Rekonsiliasi</h4>
                <div class="user-profile">
                    <span class="text-muted small">Halo, {{ auth()->user()->name }}</span>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=EE2E24&color=fff"
                        class="rounded-circle ms-2" width="40">
                </div>
            </div>

            {{-- CARD SUMMARY --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">TOTAL PO</small>
                        <h3>{{ $total_po }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">TOTAL PROJECT</small>
                        <h3>{{ $total_boq }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-success">BOQ LURUS</small>
                        <h3 class="text-success">{{ $boq_lurus }}</h3>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 border-danger border-2">
                        <small class="text-danger">BOQ SELISIH</small>
                        <h3 class="text-danger">{{ $boq_selisih }}</h3>
                    </div>
                </div>
            </div>

            {{-- TABLE --}}
<div class="card p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-header">
                <tr>
                    <th>NO</th>
                    <th>NO PO</th>
                    <th>TANGGAL UPLOAD</th>
                    <th class="text-center">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $project)
                <tr>
                    <td>{{ $data->firstItem() + $loop->index }}</td>
                    <td>{{ $project->project_name }}</td>
                    <td>{{ $project->created_at->format('d/m/Y') }}</td>
                    <td class="text-center">
                        <a href="{{ route('rekon', $project->id) }}" 
                           class="btn btn-sm btn-primary">
                            <i class="fas fa-calculator me-1"></i> Rekon
                        </a>

                        @if(auth()->user()?->canUpload())
                            <a href="{{ route('upload.edit', $project->id) }}" class="btn btn-sm btn-outline-secondary ms-2">
                                <i class="fas fa-pen-to-square me-1"></i> Edit Upload
                            </a>
                        @endif

                        @if(auth()->user()?->isSuperAdmin())
                            <form method="POST" action="{{ route('project.destroy', $project->id) }}" class="d-inline" onsubmit="return confirm('Hapus project ini beserta datanya?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger ms-2">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $data->links('pagination::bootstrap-5') }}
    </div>
</div>

        </div>

    </div>
</div>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>