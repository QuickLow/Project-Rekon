<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekonProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RekonController extends Controller
{
    public function index()
    {
        $data = RekonProject::latest()->paginate(10);

        return view('dashboard', [
            'data' => $data,
            'total' => $data->total(),
            'match' => 0,
            'mismatch' => 0,
            'listLokasi' => []
        ]);
    }

    public function create()
    {
        return view('upload');
    }

   public function store(Request $request)
{
    // TAMPILKAN ERROR JIKA ADA, JANGAN DISEMBUNYIKAN
    try {
        // VALIDASI DASAR
        $allowedExt = ['xlsx', 'xls', 'csv', 'xlsm', 'xlsb'];
        $fileRule = function (string $attribute, $value, $fail) use ($allowedExt) {
            if (!$value) {
                $fail("$attribute wajib diisi.");
                return;
            }

            // Di Windows/Laragon, deteksi MIME kadang salah (application/octet-stream),
            // jadi kita validasi pakai ekstensi file asli agar user tidak terjebak.
            $ext = strtolower((string) $value->getClientOriginalExtension());
            if (!in_array($ext, $allowedExt, true)) {
                $fail("$attribute harus bertipe: " . implode(', ', $allowedExt));
            }
        };

        $request->validate([
            'project_name' => 'required|string',
            'file_gudang'  => ['required', 'file', 'max:51200', $fileRule],
            'file_telkom'  => ['required', 'file', 'max:51200', $fileRule],
            'file_mitra'   => ['required', 'file', 'max:51200', $fileRule],
        ]);
        
        // CEK FILE SEBELUM DIPROSES
        if (!$request->hasFile('file_gudang')) {
            throw new \Exception("File Gudang tidak ditemukan!");
        }
        if (!$request->hasFile('file_telkom')) {
            throw new \Exception("File TA (Telkom Akses) tidak ditemukan!");
        }
        if (!$request->hasFile('file_mitra')) {
            throw new \Exception("File Mitra tidak ditemukan!");
        }

        $gudangFile = $request->file('file_gudang');
        $telkomFile = $request->file('file_telkom');
        $mitraFile = $request->file('file_mitra');
        
        // SIMPAN PROJECT
        $project = RekonProject::create([
            'project_name' => $request->project_name
        ]);
        
        // PARSE 3 FILE TEMPLATE (GUDANG, TA, MITRA) + VALIDASI TIPE FILE
        $rowsGudang = $this->parseTemplate($gudangFile, 'GUDANG');
        $rowsTA     = $this->parseTemplate($telkomFile, 'TA');
        $rowsMitra  = $this->parseTemplate($mitraFile, 'MITRA');

        // GABUNGKAN BERDASARKAN (MITRA, LOP, DESIGNATOR)
        $index = [];

        foreach ($rowsGudang as $r) {
            $key = strtoupper(trim($r['mitra'])) . '|' . strtoupper(trim($r['lop'])) . '|' . strtoupper(trim($r['designator']));
            if (!isset($index[$key])) {
                $index[$key] = [
                    'mitra_name' => trim($r['mitra']),
                    'lop_name' => trim($r['lop']),
                    'designator' => trim($r['designator']),
                    'qty_gudang' => 0,
                    'qty_ta' => 0,
                    'qty_mitra' => 0,
                ];
            }
            $index[$key]['qty_gudang'] += (float)($r['jumlah'] ?? 0);
        }
        foreach ($rowsTA as $r) {
            $key = strtoupper(trim($r['mitra'])) . '|' . strtoupper(trim($r['lop'])) . '|' . strtoupper(trim($r['designator']));
            if (!isset($index[$key])) {
                $index[$key] = [
                    'mitra_name' => trim($r['mitra']),
                    'lop_name' => trim($r['lop']),
                    'designator' => trim($r['designator']),
                    'qty_gudang' => 0,
                    'qty_ta' => 0,
                    'qty_mitra' => 0,
                ];
            }
            $index[$key]['qty_ta'] += (float)($r['jumlah'] ?? 0);
        }
        foreach ($rowsMitra as $r) {
            $key = strtoupper(trim($r['mitra'])) . '|' . strtoupper(trim($r['lop'])) . '|' . strtoupper(trim($r['designator']));
            if (!isset($index[$key])) {
                $index[$key] = [
                    'mitra_name' => trim($r['mitra']),
                    'lop_name' => trim($r['lop']),
                    'designator' => trim($r['designator']),
                    'qty_gudang' => 0,
                    'qty_ta' => 0,
                    'qty_mitra' => 0,
                ];
            }
            $index[$key]['qty_mitra'] += (float)($r['jumlah'] ?? 0);
        }

        // SIMPAN KE TABEL REKON_ITEMS
        $batch = [];
        foreach ($index as $k => $v) {
            $batch[] = [
                'project_id'  => $project->id,
                'mitra_name'  => $v['mitra_name'],
                'lop_name'    => $v['lop_name'],
                'designator'  => $v['designator'],
                'qty_gudang'  => $v['qty_gudang'] ?? 0,
                'qty_ta'      => $v['qty_ta'] ?? 0,
                'qty_mitra'   => $v['qty_mitra'] ?? 0,
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }
        if (!empty($batch)) {
            DB::table('rekon_items')->insert($batch);
        }

        // ARAHKAN KE HALAMAN LIST MITRA UNTUK PROYEK INI + ringkasan impor
        $summary = sprintf(
            'Upload berhasil! Project: %s. Baris: Gudang %d, TA %d, Mitra %d. Item unik tersimpan: %d.',
            $request->project_name,
            count($rowsGudang), count($rowsTA), count($rowsMitra), count($batch)
        );

        if (count($batch) === 0) {
            return back()->withInput()->with('error', 'Tidak ada baris valid yang terbaca dari file. Periksa header dan isi kolom.');
        }

        return redirect()
            ->route('rekon', $project->id)
            ->with('success', $summary);
            
    } catch (\Exception $e) {
        // TAMPILKAN ERROR DI BROWSER
        return back()
            ->withInput()
            ->with('error', 'ERROR: ' . $e->getMessage());
    }
}

private function parseTemplate($file, ?string $expectedSource = null)
{
    $disk = config('filesystems.default');
    $path = $file->store('uploads', $disk);
    $fullPath = Storage::disk($disk)->path($path);

    if (!is_string($fullPath) || $fullPath === '' || !file_exists($fullPath)) {
        throw new \Exception('File upload sementara tidak ditemukan: ' . (string)$fullPath);
    }

    $ext = strtolower($file->getClientOriginalExtension() ?? pathinfo($fullPath, PATHINFO_EXTENSION));

    // XLSX/XLSM/XLSB butuh ekstensi PHP zip (ZipArchive) karena formatnya ZIP.
    if (in_array($ext, ['xlsx', 'xlsm', 'xlsb'], true) && !class_exists('ZipArchive')) {
        throw new \Exception(
            'PHP extension ZIP (ZipArchive) belum aktif, jadi file Excel (.xlsx/.xlsm/.xlsb) tidak bisa dibaca. ' .
            'Aktifkan extension=zip di php.ini Laragon, atau upload pakai template CSV.'
        );
    }
    if ($ext === 'csv') {
        $content = @file_get_contents($fullPath) ?: '';
        $firstLine = strtok($content, "\r\n");
        $countComma = substr_count((string)$firstLine, ',');
        $countSemi  = substr_count((string)$firstLine, ';');
        $delimiter = $countSemi >= $countComma ? ';' : ',';

        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
        $reader->setDelimiter($delimiter);
        $reader->setEnclosure('"');
        $reader->setSheetIndex(0);
        $spreadsheet = $reader->load($fullPath);
    } else {
        try {
            $spreadsheet = IOFactory::load($fullPath);
        } catch (\Throwable $e) {
            // Pesan lebih jelas untuk kasus ZipArchive / lingkungan Windows
            $msg = $e->getMessage();
            if (!class_exists('ZipArchive')) {
                throw new \Exception(
                    'Gagal membaca Excel. Penyebab paling umum: ZipArchive belum aktif di PHP. ' .
                    'Aktifkan extension=zip di php.ini Laragon, atau upload pakai template CSV.'
                );
            }
            throw new \Exception('Gagal membaca file: ' . $msg);
        }
    }
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    // Validasi tipe file agar Gudang/TA/Mitra tidak tertukar.
    // Template terbaru punya baris 1: REKON_TEMPLATE;GUDANG (atau TA/MITRA)
    $expectedSource = $expectedSource ? strtoupper(trim($expectedSource)) : null;
    $detectedSource = null;
    $maxSourceCheck = min(3, count($rows));
    for ($idx = 1; $idx <= $maxSourceCheck; $idx++) {
        $r = $rows[$idx] ?? [];
        $marker = strtoupper(trim((string)($r['A'] ?? '')));
        if (in_array($marker, ['REKON_TEMPLATE', 'TEMPLATE', 'SOURCE', 'TIPE FILE'], true)) {
            $value = strtoupper(trim((string)($r['B'] ?? '')));
            if ($value !== '') {
                // Normalisasi beberapa variasi penulisan
                if (in_array($value, ['TELKOM', 'TELKOM AKSES', 'TELKOMAKSES'], true)) {
                    $value = 'TA';
                }
                $detectedSource = $value;
                break;
            }
        }
    }

    if ($expectedSource !== null) {
        if ($detectedSource === null) {
            throw new \Exception(
                'File yang diupload tidak memiliki penanda tipe (REKON_TEMPLATE). ' .
                'Silakan download template terbaru dari halaman Upload, isi data, lalu upload kembali.'
            );
        }

        if ($detectedSource !== $expectedSource) {
            $labelExpected = $expectedSource === 'TA' ? 'TA (Telkom Akses)' : $expectedSource;
            $labelDetected = $detectedSource === 'TA' ? 'TA (Telkom Akses)' : $detectedSource;
            throw new \Exception(
                sprintf(
                    'File yang Anda upload pada kolom %s terdeteksi sebagai %s. ' .
                    'Kemungkinan file tertukar. Silakan upload file yang sesuai.',
                    $labelExpected,
                    $labelDetected
                )
            );
        }
    }

    // Deteksi header kolom (coba di 3 baris awal). Jika tidak ditemukan, gunakan fallback A,B,C,D.
    $colMap = ['mitra' => null, 'lop' => null, 'designator' => null, 'jumlah' => null];
    $headerRowIndex = null;
    $maxCheck = min(3, count($rows));
    for ($idx = 1; $idx <= $maxCheck; $idx++) {
        $headerRow = $rows[$idx] ?? [];
        foreach ($headerRow as $col => $val) {
            $label = strtoupper(trim((string)$val));
            if (!$colMap['mitra'] && in_array($label, ['NAMA MITRA','MITRA'])) $colMap['mitra'] = $col;
            if (!$colMap['lop'] && in_array($label, ['NAMA LOP','LOP'])) $colMap['lop'] = $col;
            if (!$colMap['designator'] && in_array($label, ['DESIGNATOR','DESIGNATOR ITEM'])) $colMap['designator'] = $col;
            if (!$colMap['jumlah'] && in_array($label, ['JUMLAH','QTY','QUANTITY'])) $colMap['jumlah'] = $col;
        }
        if ($colMap['mitra'] && $colMap['lop'] && $colMap['designator'] && $colMap['jumlah']) {
            $headerRowIndex = $idx;
            break;
        }
    }
    // Fallback jika header tidak ketemu: asumsikan A,B,C,D
    $colMap['mitra'] = $colMap['mitra'] ?? 'A';
    $colMap['lop'] = $colMap['lop'] ?? 'B';
    $colMap['designator'] = $colMap['designator'] ?? 'C';
    $colMap['jumlah'] = $colMap['jumlah'] ?? 'D';

    $out = [];
    $lastMitra = '';
    $lastLop = '';
    foreach ($rows as $i => $row) {
        if ($headerRowIndex !== null) {
            if ($i <= $headerRowIndex) continue;
        } else {
            if ($i === 1) continue; // skip header (fallback)
        }
        $rawMitra = trim((string)($row[$colMap['mitra']] ?? ''));
        $rawLop = trim((string)($row[$colMap['lop']] ?? ''));
        $mitra = $rawMitra !== '' ? $rawMitra : $lastMitra;
        $lop = $rawLop !== '' ? $rawLop : $lastLop;
        if ($mitra !== '') $lastMitra = $mitra;
        if ($lop !== '') $lastLop = $lop;
        $designator = trim((string)($row[$colMap['designator']] ?? ''));
        $jumlah = $row[$colMap['jumlah']] ?? 0;
        if ($designator === '' && ($jumlah === '' || $jumlah === null)) continue;
        // Bersihkan jumlah numerik (handle koma sebagai desimal)
        if (is_string($jumlah)) {
            $jumlah = str_replace(',', '.', $jumlah);
            $jumlah = preg_replace('/[^0-9\.\-]/', '', $jumlah);
            if ($jumlah === '' || $jumlah === '-') $jumlah = 0;
        }
        $out[] = [
            'mitra' => $mitra,
            'lop' => $lop,
            'designator' => $designator,
            'jumlah' => is_numeric($jumlah) ? (float)$jumlah : 0,
        ];
    }

    // Hapus file upload sementara (opsional)
    try {
        Storage::disk($disk)->delete($path);
    } catch (\Throwable $e) {
        // ignore
    }

    return $out;
}

   public function mitraList($id)
{
    // Aggregasi nama mitra dan jumlah LOP dalam proyek
    $items = DB::table('rekon_items')
        ->select('mitra_name', DB::raw('COUNT(DISTINCT lop_name) as lop_count'))
        ->where('project_id', $id)
        ->groupBy('mitra_name')
        ->orderBy('mitra_name')
        ->get();

    return view('mitra_list', [
        'project' => RekonProject::findOrFail($id),
        'mitras'  => $items,
    ]);
}

   public function lopList($id, $mitra)
{
    // Daftar LOP untuk mitra tertentu
    $items = DB::table('rekon_items')
        ->select('lop_name')
        ->where('project_id', $id)
        ->where('mitra_name', $mitra)
        ->groupBy('lop_name')
        ->orderBy('lop_name')
        ->get();

    return view('lop_list', [
        'project' => RekonProject::findOrFail($id),
        'mitra'   => $mitra,
        'lops'    => $items,
    ]);
}

   public function detail($id, $mitra, $lop)
{
    // Detail designator per LOP & Mitra dengan selisih
    $rows = DB::table('rekon_items')
        ->where('project_id', $id)
        ->where('mitra_name', $mitra)
        ->where('lop_name', $lop)
        ->where(function ($q) {
            $q->where('qty_gudang', '!=', 0)
              ->orWhere('qty_ta', '!=', 0)
              ->orWhere('qty_mitra', '!=', 0);
        })
        ->orderBy('designator')
        ->get();

    $detail = [];
    foreach ($rows as $r) {
        $detail[] = [
            'designator' => $r->designator,
            'gudang'     => (float)$r->qty_gudang,
            'ta'         => (float)$r->qty_ta,
            'mitra'      => (float)$r->qty_mitra,
            'selisih_gudang_mitra' => (float)$r->qty_gudang - (float)$r->qty_mitra,
            'selisih_ta_mitra'     => (float)$r->qty_ta - (float)$r->qty_mitra,
        ];
    }

    return view('detail', [
        'project' => RekonProject::findOrFail($id),
        'mitra'   => $mitra,
        'lop'     => $lop,
        'detail'  => $detail,
    ]);
}


}
