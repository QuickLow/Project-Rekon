<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rekon - Designator</title>
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
        .badge-match { background-color: #10B981; color: white; }
        .badge-mismatch { background-color: #EE2E24; color: white; }

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
            <h5 class="offcanvas-title" id="mobileSidebarLabel"><i class="fas fa-network-wired me-2"></i>SIRMA</h5>
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
                <i class="fas fa-network-wired"></i> SIRMA
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
            <div class="d-flex d-md-none align-items-center justify-content-between mb-3">
                <button class="btn btn-outline-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="fw-bold">SIRMA</div>
                <div style="width:42px;"></div>
            </div>
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('rekon', $project->id) }}"><i class="fas fa-users"></i>Daftar Mitra</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('rekon.mitra', [$project->id, $mitra]) }}"><i class="fas fa-list"></i>List LOP</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page"><i class="fas fa-info-circle"></i>Detail Designator</li>
                </ol>
            </nav>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>Detail Designator</h4>
                    <div class="text-muted small">No PO: {{ $project->project_name }}, Upload: {{ $project->created_at->format('d/m/Y') }} | Mitra: {{ $mitra }} | LOP: {{ $lop }}</div>
                </div>
            </div>
            <div class="card p-4">
                @php
                    $fmtQty = function ($n) {
                        $n = (float)($n ?? 0);
                        if ($n == 0.0) return '';
                        $s = number_format($n, 4, '.', '');
                        return rtrim(rtrim($s, '0'), '.');
                    };
                    $fmtDiff = function ($n) {
                        $n = (float)($n ?? 0);
                        $s = number_format($n, 4, '.', '');
                        $s = rtrim(rtrim($s, '0'), '.');
                        return $s === '' ? '0' : $s;
                    };
                @endphp
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-header">
                            <tr>
                                <th>MITRA</th>
                                <th>LOP</th>
                                <th>DESIGNATOR</th>
                                <th class="text-center">JML GUDANG</th>
                                <th class="text-center">JML TA</th>
                                <th class="text-center">JML MITRA</th>
                                <th class="text-center">SELISIH GUDANG - MITRA</th>
                                <th class="text-center">SELISIH TA - MITRA</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($detail as $d)
                            <tr>
                                <td class="align-top">{{ $loop->first ? $mitra : '' }}</td>
                                <td class="align-top">{{ $loop->first ? $lop : '' }}</td>
                                <td>{{ $d['designator'] }}</td>
                                <td class="text-center">{{ $fmtQty($d['gudang'] ?? 0) }}</td>
                                <td class="text-center">{{ $fmtQty($d['ta'] ?? 0) }}</td>
                                <td class="text-center">{{ $fmtQty($d['mitra'] ?? 0) }}</td>
                                <td class="text-center">
                                    <span class="badge {{ ($d['selisih_gudang_mitra'] == 0) ? 'badge-match' : 'badge-mismatch' }}">
                                        {{ $fmtDiff($d['selisih_gudang_mitra'] ?? 0) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge {{ ($d['selisih_ta_mitra'] == 0) ? 'badge-match' : 'badge-mismatch' }}">
                                        {{ $fmtDiff($d['selisih_ta_mitra'] ?? 0) }}
                                    </span>
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
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</html>