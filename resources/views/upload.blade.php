<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Data - Rekon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { background-color: #F3F4F6; }
        .sidebar { background-color: #EE2E24; min-height: 100vh; color: white; }
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

        .sidebar-brand { font-size: 1.5rem; font-weight: bold; padding: 1.5rem 1rem; letter-spacing: 2px; }

        .upload-box {
            border: 2px dashed #ccc;
            border-radius: 15px;
            background-color: white;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .upload-box:hover {
            border-color: #EE2E24;
            background-color: #fff5f5;
        }

        .upload-icon {
            font-size: 3rem;
            color: #EE2E24;
            margin-bottom: 15px;
        }

        .form-control[type="file"] {
            display: none;
        }
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
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                    </li>
                    @if(auth()->user()?->canUpload())
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('upload') }}">
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
                <i class="fas fa-network-wired"></i> SIRMA
            </div>
            <ul class="nav flex-column flex-grow-1">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                @if(auth()->user()?->canUpload())
                    <li class="nav-item">
                        <a class="nav-link active" href="{{ route('upload') }}">
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

        <div class="col-12 col-md-10 p-4 p-md-5">
            <div class="d-flex d-md-none align-items-center justify-content-between mb-3">
                <button class="btn btn-outline-danger" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar" aria-controls="mobileSidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="fw-bold">SIRMA</div>
                <div style="width:42px;"></div>
            </div>
            <h3 class="mb-4">
                @if(!empty($project))
                    Upload Ulang (Timpa) - {{ $project->project_name }}
                @else
                    Upload Data Baru
                @endif
            </h3>

            {{-- ERROR BLOCK --}}
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">


                @csrf

                @if(!empty($project))
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                @endif

                <div class="card p-4 mb-4 border-0 shadow-sm">
                      <label class="fw-bold mb-2">Nomor PO</label>
                    <input type="text"
                           name="project_name"
                           class="form-control form-control-lg"
                          value="{{ old('project_name', !empty($project) ? $project->project_name : '') }}"
                          placeholder="Contoh: 5500012381"
                           required>
                </div>

                <div class="row">
                    <!-- GUDANG -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white fw-bold text-center py-3">
                                DATA GUDANG
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Template: </small>
                                    <a class="btn btn-sm btn-outline-danger" href="/templates/gudang_template.csv" download>
                                        Download Template
                                    </a>
                                </div>
                                <label for="file_gudang" class="upload-box w-100">
                                    <i class="fas fa-file-excel upload-icon"></i>
                                    <h5>Klik untuk Upload Excel/CSV</h5>
                                    <p class="text-muted small">Format: .xlsx / .xls / .csv</p>
                                    <div id="preview_gudang" class="text-success fw-bold mt-2"></div>
                                </label>
                                    <input type="file" accept=".xlsx,.xls,.csv,.xlsm,.xlsb"
                                       id="file_gudang"
                                       name="file_gudang"
                                       class="form-control"
                                       required
                                       onchange="showFileName('file_gudang', 'preview_gudang')">
                            </div>
                        </div>
                    </div>
                    <!-- TELKOM -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white fw-bold text-center py-3">
                                DATA TA (Telkom Akses)
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Template: </small>
                                    <a class="btn btn-sm btn-outline-danger" href="/templates/ta_template.csv" download>
                                        Download Template
                                    </a>
                                </div>
                                <label for="file_telkom" class="upload-box w-100">
                                    <i class="fas fa-file-excel upload-icon"></i>
                                    <h5>Klik untuk Upload Excel/CSV</h5>
                                    <p class="text-muted small">Format: .xlsx / .xls / .csv</p>
                                    <div id="preview_telkom" class="text-success fw-bold mt-2"></div>
                                </label>
                                    <input type="file" accept=".xlsx,.xls,.csv,.xlsm,.xlsb"
                                       id="file_telkom"
                                       name="file_telkom"
                                       class="form-control"
                                       required
                                       onchange="showFileName('file_telkom', 'preview_telkom')">
                            </div>
                        </div>
                    </div>

                    <!-- MITRA -->
                    <div class="col-md-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white fw-bold text-center py-3">
                                DATA MITRA
                            </div>
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">Template: </small>
                                    <a class="btn btn-sm btn-outline-danger" href="/templates/mitra_template.csv" download>
                                        Download Template
                                    </a>
                                </div>
                                <label for="file_mitra" class="upload-box w-100">
                                    <i class="fas fa-file-excel upload-icon"></i>
                                    <h5>Klik untuk Upload Excel/CSV</h5>
                                    <p class="text-muted small">Format: .xlsx / .xls / .csv</p>
                                    <div id="preview_mitra" class="text-success fw-bold mt-2"></div>
                                </label>
                                    <input type="file" accept=".xlsx,.xls,.csv,.xlsm,.xlsb"
                                       id="file_mitra"
                                       name="file_mitra"
                                       class="form-control"
                                       required
                                       onchange="showFileName('file_mitra', 'preview_mitra')">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <button type="submit" class="btn btn-danger btn-lg px-5 py-3 rounded-pill fw-bold shadow">
                        <i class="fas fa-rocket me-2"></i> PROSES REKONSILIASI
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function showFileName(inputId, previewId) {
        let input = document.getElementById(inputId);
        let preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            preview.innerText = "File Terpilih: " + input.files[0].name;
        }
    }
</script>

</body>
</html>
