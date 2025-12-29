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
        .sidebar .nav-link { color: rgba(255,255,255,0.8); margin-bottom: 5px; border-radius: 10px; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: white; color: #EE2E24; font-weight: bold; }
        .sidebar-brand { font-size: 1.5rem; font-weight: bold; padding: 1.5rem 1rem; letter-spacing: 2px; }
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
                <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i> Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('upload') }}"><i class="fas fa-upload me-2"></i> Upload Data</a></li>
            </ul>
        </div>
        <div class="col-md-10 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4>Detail Designator</h4>
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
                                <th class="text-center">SELISIH GUDANG vs MITRA</th>
                                <th class="text-center">SELISIH TA vs MITRA</th>
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
</html>