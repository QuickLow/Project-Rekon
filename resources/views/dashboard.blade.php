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
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: white;
            color: #EE2E24;
            font-weight: bold;
        }
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
    <div class="row">
        <div class="col-md-2 sidebar p-3">
            <div class="sidebar-brand mb-4">
                <i class="fas fa-network-wired"></i> REKON
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="#">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/upload') }}">
                        <i class="fas fa-upload me-2"></i> Upload Data
                    </a>
                </li>
                <li class="nav-item mt-5">
                    <a class="nav-link" href="#">
                        <i class="fas fa-sign-out-alt me-2"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-md-10 p-4">

            {{-- ALERT --}}
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Dashboard Rekonsiliasi</h4>
                <div class="user-profile">
                    <span class="text-muted small">Halo, Harik</span>
                    <img src="https://ui-avatars.com/api/?name=Harik&background=EE2E24&color=fff"
                        class="rounded-circle ms-2" width="40">
                </div>
            </div>

            {{-- CARD SUMMARY --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-muted">TOTAL PROJECT</small>
                        <h3>{{ $total }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3">
                        <small class="text-success">MATCH</small>
                        <h3>{{ $match }}</h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card p-3 border-danger border-2">
                        <small class="text-danger">MISMATCH</small>
                        <h3 class="text-danger">{{ $mismatch }}</h3>
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
                    <th>PROJECT</th>
                    <th>TANGGAL</th>
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
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $data->links() }}
    </div>
</div>

        </div>

    </div>
</div>

</body>
</html>