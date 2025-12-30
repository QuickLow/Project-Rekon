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
        .sidebar .nav-link { color: rgba(255,255,255,0.8); margin-bottom: 5px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background-color: white;
            color: #EE2E24;
            font-weight: bold;
        }

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
    <div class="row">
        <div class="col-md-2 sidebar p-3">
            <div class="mb-4 p-3 fs-4 fw-bold">
                <i class="fas fa-network-wired"></i> REKON
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('upload') }}">
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

        <div class="col-md-10 p-5">
            <h3 class="mb-4">Upload Data Baru</h3>

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

            <form action="/upload" method="POST" enctype="multipart/form-data">


                @csrf

                <div class="card p-4 mb-4 border-0 shadow-sm">
                    <label class="fw-bold mb-2">Nama Batch / Project</label>
                    <input type="text"
                           name="project_name"
                           class="form-control form-control-lg"
                           {{-- placeholder="Contoh: Rekon STO Pontianak Oktober 2025" --}}
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
                                    <p class="text-muted small mb-0">Gunakan template terbaru (baris <b>REKON_TEMPLATE</b> jangan dihapus). File yang tertukar akan ditolak.</p>
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
                                    <p class="text-muted small mb-0">Gunakan template terbaru (baris <b>REKON_TEMPLATE</b> jangan dihapus). File yang tertukar akan ditolak.</p>
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
                                    <p class="text-muted small mb-0">Gunakan template terbaru (baris <b>REKON_TEMPLATE</b> jangan dihapus). File yang tertukar akan ditolak.</p>
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
