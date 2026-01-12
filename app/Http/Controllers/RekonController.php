<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RekonProject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RekonController extends Controller
{
    private function normalizeDesignatorKey(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;
        return strtoupper($value);
    }

    /**
     * Map designator Gudang (alias) -> designator versi TA/Mitra (canonical).
     *
     * Opsional: kalau file mapping ada di storage/app/designator_map_gudang.csv,
     * formatnya: canonical,gudang_alias (gudang_alias boleh dipisah koma untuk banyak alias).
     */
    private function loadGudangDesignatorMap(): array
    {
        $map = [];

        // 1) Coba baca dari file CSV agar gampang ditambah tanpa ubah kode.
        $csvPath = storage_path('app/designator_map_gudang.csv');
        if (is_file($csvPath)) {
            $lines = @file($csvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
            foreach ($lines as $line) {
                $line = trim((string) $line);
                if ($line === '' || str_starts_with($line, '#')) continue;
                $cols = str_getcsv($line);
                $canonical = trim((string) ($cols[0] ?? ''));
                $aliasesRaw = trim((string) ($cols[1] ?? ''));
                if ($canonical === '' || $aliasesRaw === '') continue;

                $canonicalKey = $this->normalizeDesignatorKey($canonical);
                $map[$canonicalKey] = $canonical; // canonical tetap map ke dirinya

                $aliases = array_map('trim', explode(',', $aliasesRaw));
                foreach ($aliases as $alias) {
                    if ($alias === '') continue;
                    $map[$this->normalizeDesignatorKey($alias)] = $canonical;
                }
            }
        }

        // 2) Fallback hardcoded (sesuai daftar yang user kirim via screenshot).
        // Hanya pasangan yang jelas (1:1) agar tidak menebak yang ambigu.
        $fallbackPairs = [
            // canonical => gudang
            'PL-RING' => 'KLEM-RING-5-LUBANG',
            'PU-AS-DE-50/70' => 'DEAD-END-CLAMP',
            'PU-AS-SC' => 'SS-TIANG-CLAMP',
            'PU-AS-HL' => 'HELICAL-GRIP-LKP',
            'ODP SOLID-PB-16 AS' => 'ODP-SOLID-1-16-L',
            'ODP SOLID-PB-8 AS' => 'ODP-SOLID-8-L',
            'SLACK-SUPP' => 'SLACK-S',
            'DD-HDPE-40/33' => 'DD-HDPE-40-1C',
        ];

        foreach ($fallbackPairs as $canonical => $alias) {
            $canonical = trim((string) $canonical);
            $alias = trim((string) $alias);
            if ($canonical === '' || $alias === '') continue;
            $map[$this->normalizeDesignatorKey($canonical)] = $canonical;
            $map[$this->normalizeDesignatorKey($alias)] = $canonical;
        }

        return $map;
    }

    private function mapGudangDesignatorToCanonical(string $designator): string
    {
        $designator = trim($designator);
        if ($designator === '') return '';

        $map = $this->loadGudangDesignatorMap();

        // Jika cell mengandung beberapa nama dipisah koma, coba map per token.
        if (str_contains($designator, ',')) {
            $tokens = array_values(array_filter(array_map('trim', explode(',', $designator)), fn ($t) => $t !== ''));
            $canonicals = [];
            foreach ($tokens as $t) {
                $key = $this->normalizeDesignatorKey($t);
                $canonicals[] = $map[$key] ?? $t;
            }
            $unique = array_values(array_unique($canonicals));
            if (count($unique) === 1) {
                return (string) $unique[0];
            }
            return $designator;
        }

        $key = $this->normalizeDesignatorKey($designator);
        return $map[$key] ?? $designator;
    }

    public function index()
    {
        $data = RekonProject::latest()->paginate(10);

        $totalPo = RekonProject::count();

        $lopAgg = DB::table('rekon_items')
            ->select(
                'project_id',
                'mitra_name',
                'lop_name',
                DB::raw('MAX(CASE WHEN COALESCE(qty_gudang, -1) != COALESCE(qty_mitra, -1) OR COALESCE(qty_ta, -1) != COALESCE(qty_mitra, -1) THEN 1 ELSE 0 END) as has_diff')
            )
            ->groupBy('project_id', 'mitra_name', 'lop_name');

        $totalBoq = DB::query()->fromSub($lopAgg, 'lops')->count();
        $boqSelisih = DB::query()->fromSub($lopAgg, 'lops')->where('has_diff', 1)->count();
        $boqLurus = DB::query()->fromSub($lopAgg, 'lops')->where('has_diff', 0)->count();

        return view('dashboard', [
            'data' => $data,
            'total_po' => $totalPo,
            'total_boq' => $totalBoq,
            'boq_lurus' => $boqLurus,
            'boq_selisih' => $boqSelisih,
            'listLokasi' => []
        ]);
    }

    public function destroyProject($id)
    {
        $project = RekonProject::findOrFail($id);
        $project->delete();

        return redirect()->route('dashboard')->with('success', 'Project berhasil dihapus.');
    }

    public function create()
    {
        return view('upload', [
            'project' => null,
        ]);
    }

    public function editUpload($id)
    {
        $project = RekonProject::findOrFail($id);

        return view('upload', [
            'project' => $project,
        ]);
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
            'project_id'   => 'nullable|integer|exists:rekon_projects,id',
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

        // Validasi berdasarkan NAMA FILE agar tidak tertukar.
        // Contoh nama file yang disarankan:
        // - gudang_2025-12.csv
        // - ta_2025-12.csv / telkom_akses_2025-12.csv
        // - mitra_2025-12.csv
        $detectSource = function (string $filename): ?string {
            $name = strtoupper($filename);
            // Normalisasi sederhana: hilangkan spasi agar "TELKOM AKSES" juga cocok.
            $flat = str_replace(' ', '', $name);

            if (str_contains($flat, 'GUDANG')) return 'GUDANG';
            if (str_contains($flat, 'MITRA')) return 'MITRA';
            if (str_contains($flat, 'TA') || str_contains($flat, 'TELKOM') || str_contains($flat, 'TELKOMAKSES') || str_contains($flat, 'TELKOM_AKSES')) return 'TA';
            return null;
        };

        $checkFile = function ($file, string $expected) use ($detectSource) {
            $expected = strtoupper($expected);
            $name = (string) $file->getClientOriginalName();
            $detected = $detectSource($name);
            if ($detected === null) {
                throw new \Exception(
                    "Nama file untuk {$expected} tidak terdeteksi. " .
                    "Mohon beri nama file mengandung kata: " .
                    ($expected === 'TA' ? "ta / telkom_akses" : strtolower($expected)) .
                    ". Nama file Anda: {$name}"
                );
            }
            if ($detected !== $expected) {
                $labelExpected = $expected === 'TA' ? 'TA (Telkom Akses)' : $expected;
                $labelDetected = $detected === 'TA' ? 'TA (Telkom Akses)' : $detected;
                throw new \Exception(
                    "File tertukar: input {$labelExpected} terdeteksi sebagai {$labelDetected}. " .
                    "Nama file: {$name}"
                );
            }
        };

        $checkFile($gudangFile, 'GUDANG');
        $checkFile($telkomFile, 'TA');
        $checkFile($mitraFile, 'MITRA');
        
        $overwriteProjectId = $request->input('project_id');
        $overwriteProject = null;
        if (!empty($overwriteProjectId)) {
            $overwriteProject = RekonProject::findOrFail($overwriteProjectId);
        }
        
        // PARSE 3 FILE TEMPLATE (GUDANG, TA, MITRA)
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

        $uniqueCount = count($index);

        if ($uniqueCount === 0) {
            return back()->withInput()->with('error', 'Tidak ada baris valid yang terbaca dari file. Periksa header dan isi kolom.');
        }

        $project = null;
        DB::transaction(function () use ($overwriteProject, $request, $index, &$project) {
            if ($overwriteProject) {
                $overwriteProject->project_name = $request->project_name;
                $overwriteProject->save();

                DB::table('rekon_items')->where('project_id', $overwriteProject->id)->delete();
                $project = $overwriteProject;
            } else {
                $project = RekonProject::create([
                    'project_name' => $request->project_name,
                ]);
            }

            $batch = [];
            foreach ($index as $v) {
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
        });

        // ARAHKAN KE HALAMAN LIST MITRA UNTUK PROYEK INI + ringkasan impor
        $summary = sprintf(
            '%s berhasil! Project: %s. Baris: Gudang %d, TA %d, Mitra %d. Item unik tersimpan: %d.',
            $overwriteProject ? 'Upload ulang' : 'Upload',
            $request->project_name,
            count($rowsGudang), count($rowsTA), count($rowsMitra), $uniqueCount
        );

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

private function parseTemplate($file, ?string $source = null)
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
        
        // Normalisasi: hapus prefix "M-" di depan designator (semua file) agar merge key konsisten
        if (str_starts_with($designator, 'M-')) {
            $designator = substr($designator, 2);
        }
        
        if (strtoupper((string) $source) === 'GUDANG' && $designator !== '') {
            $designator = $this->mapGudangDesignatorToCanonical($designator);
        }
        // Beberapa file Excel hasil copy/merge membuat kolom LOP kosong,
        // tapi kolom DESIGNATOR berisi gabungan: "<LOP> <DESIGNATOR>" (contoh: "MD... M-...").
        // Jika terdeteksi, pecah otomatis agar 1 mitra bisa punya banyak LOP.
        if ($lop === '' && $designator !== '') {
            $normalized = preg_replace('/\x{00A0}/u', ' ', $designator) ?? $designator; // non-breaking space
            $pos = strpos($normalized, 'M-');
            if ($pos !== false && $pos > 0) {
                $possibleLop = trim(substr($normalized, 0, $pos));
                $possibleDesignator = trim(substr($normalized, $pos));
                if ($possibleLop !== '' && $possibleDesignator !== '') {
                    $lop = $possibleLop;
                    $lastLop = $lop;
                    $designator = $possibleDesignator;
                }
            }
        }
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
    // Pastikan mitra memang ada di project ini (kalau tidak, 404)
    $exists = DB::table('rekon_items')
        ->where('project_id', $id)
        ->where('mitra_name', $mitra)
        ->exists();
    if (!$exists) {
        abort(404);
    }

    // Daftar LOP untuk mitra tertentu + status mismatch (cek detail designator)
    $items = DB::table('rekon_items')
        ->select(
            'lop_name',
            DB::raw('MAX(CASE WHEN qty_gudang != qty_mitra OR qty_ta != qty_mitra THEN 1 ELSE 0 END) as has_diff')
        )
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
    // Pastikan kombinasi mitra + lop memang ada di project ini (kalau tidak, 404)
    $exists = DB::table('rekon_items')
        ->where('project_id', $id)
        ->where('mitra_name', $mitra)
        ->where('lop_name', $lop)
        ->exists();
    if (!$exists) {
        abort(404);
    }

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
