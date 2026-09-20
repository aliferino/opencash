<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Imports\SchedulesImport;
use App\Models\CashSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import massal JADWAL TAGIHAN (bulk add) — melengkapi CRUD di
 * `CashScheduleController`.
 *
 * Kolom yang dibaca hanya tiga: Deskripsi, Jatuh Tempo, Nominal. Itu persis
 * kolom yang dimiliki `cash_schedules`, jadi tidak ada data lain yang perlu
 * ditebak.
 *
 * Tiga bentuk berkas sama-sama diterima:
 *   1. Tiga kolom terpisah (A/B/C) — bentuk normal.
 *   2. CSV asli (dipisah koma/titik koma/tab).
 *   3. SATU kolom berisi teks CSV — kejadian umum waktu orang menempel
 *      (paste) hasil "Salin contoh" ke Excel, di mana semua teks masuk ke
 *      kolom A saja. Bentuk ini dipecah di sini dengan aturan: dua kolom
 *      terakhir = Jatuh Tempo + Nominal, sisanya = Deskripsi. Jadi deskripsi
 *      yang mengandung koma (mis. "Kas, minggu ke-3") tetap utuh.
 *
 * Judul kolom tidak kaku: dicocokkan lewat daftar sinonim
 * (`SchedulesImport::ALIASES`) dan boleh ada di beberapa baris pertama, jadi
 * baris kosong atau baris judul tambahan di atas tidak masalah.
 *
 * Aturan all-or-nothing sama seperti import lain: SEMUA baris divalidasi
 * dulu, dan kalau ada satu baris salah tidak ada yang disimpan — error
 * dilaporkan per baris ("Baris 4: ...") supaya bendahara tidak menebak
 * data mana yang sudah masuk.
 */
class CashScheduleImportController extends Controller
{
    public function store(Request $request)
    {
        $treasurer = $request->user();

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:4096'],
        ]);

        $import = new SchedulesImport($this->detectRawDelimiter($request->file('file')));
        Excel::import($import, $request->file('file'));

        if ($import->rows->isEmpty()) {
            return response()->json([
                'message' => 'Berkas kosong — tidak ada baris data yang bisa dibaca.',
            ], 422);
        }

        $grid = $this->explodeSingleColumn($import->rows);
        $heading = $this->detectHeading($grid);

        if ($heading === null) {
            return response()->json([
                'message' => 'Judul kolom tidak ditemukan. Pastikan ada baris berisi kolom: '
                    .implode(', ', $this->columnLabels()).'.',
            ], 422);
        }

        [$map, $dataStart] = $heading;
        $split = $this->splitPlan($grid);

        $rows = [];
        $errors = [];
        // deteksi baris kembar DI DALAM berkas (deskripsi + jatuh tempo sama),
        // supaya tidak ada tagihan dobel yang masuk tanpa disadari.
        $seen = [];

        for ($i = $dataStart; $i < $grid->count(); $i++) {
            $line = $i + 1; // nomor baris di berkas (1 = baris pertama)
            [$description, $dueDateRaw, $amountRaw] = $this->extract($grid[$i], $map, $split);

            $dueDate = $this->toDate($dueDateRaw);
            $amount = $this->toInt($amountRaw);

            // baris yang benar-benar kosong dilewati, bukan dianggap error
            if ($description === '' && $dueDate === null && $amount === null) {
                continue;
            }

            if ($description === '') {
                $errors[] = "Baris {$line}: deskripsi kosong.";
                continue;
            }

            if (mb_strlen($description) > 255) {
                $errors[] = "Baris {$line}: deskripsi lebih dari 255 karakter.";
                continue;
            }

            if ($dueDate === null) {
                $errors[] = "Baris {$line}: jatuh tempo tidak dikenali (pakai format dd/mm/yyyy atau yyyy-mm-dd).";
                continue;
            }

            if ($amount === null || $amount < 0) {
                $errors[] = $this->isDecimalNumber($amountRaw)
                    ? "Baris {$line}: nominal terbaca desimal ({$amountRaw}) — Excel menafsirkan titik sebagai pemisah desimal. Tulis angka polos tanpa titik/koma (mis. 12500)."
                    : "Baris {$line}: nominal harus angka dan tidak boleh negatif.";
                continue;
            }

            // Kembar = deskripsi SAMA **dan** jatuh tempo SAMA. Dua tagihan
            // berbeda dengan tanggal yang sama itu wajar (mis. kas mingguan
            // plus iuran tambahan di hari yang sama), jadi tidak ditolak.
            $key = mb_strtolower($description).'|'.$dueDate;
            if (isset($seen[$key])) {
                $errors[] = "Baris {$line}: tagihan \"{$description}\" dengan jatuh tempo yang sama sudah ada di baris {$seen[$key]}.";
                continue;
            }

            $seen[$key] = $line;

            $rows[] = [
                'group_id' => $treasurer->group_id,
                'description' => $description,
                'due_date' => $dueDate,
                'amount' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($rows === [] && $errors === []) {
            return response()->json([
                'message' => 'Tidak ada baris data setelah baris judul kolom.',
            ], 422);
        }

        if ($errors !== []) {
            return response()->json([
                'message' => 'Import dibatalkan, '.count($errors).' baris bermasalah. Tidak ada data yang disimpan.',
                'errors' => array_slice($errors, 0, 20),
                'total_errors' => count($errors),
            ], 422);
        }

        DB::transaction(fn () => CashSchedule::insert($rows));

        return response()->json([
            'message' => count($rows).' jadwal tagihan berhasil diimport.',
            'created' => count($rows),
        ], 201);
    }

    /**
     * Deteksi pemisah CSV dari ISI MENTAH berkas, lalu dipakai untuk memaksa
     * pembaca CSV. Tanpa ini, pembaca menebak sendiri dan pernah salah
     * menebak spasi saat berkas memuat baris kosong.
     *
     * Untuk berkas .xlsx/.xls (bukan teks) selalu pakai koma — pemisah tidak
     * relevan karena sel sudah terpisah sendiri.
     *
     * @param  \Illuminate\Http\UploadedFile  $file
     */
    private function detectRawDelimiter($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, ['csv', 'txt'], true)) {
            return ',';
        }

        $content = (string) file_get_contents($file->getRealPath());
        if ($content === '') {
            return ',';
        }

        // buang BOM supaya tidak mengganggu deteksi judul kolom
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content) ?? $content;

        $lines = array_slice(preg_split('/\r\n|\r|\n/', $content) ?: [], 0, 20);

        $best = ',';
        $bestCount = 0;

        foreach (SchedulesImport::DELIMITERS as $delimiter) {
            $count = 0;
            foreach ($lines as $line) {
                $count += substr_count($line, $delimiter);
            }

            if ($count > $bestCount) {
                $best = $delimiter;
                $bestCount = $count;
            }
        }

        return $best;
    }

    /**
     * Kalau berkas cuma punya satu kolom berisi teks dipisah koma/titik
     * koma/tab (hasil paste CSV ke Excel), pecah tiap baris jadi kolom.
     * Kalau sudah multi-kolom, kembalikan apa adanya.
     *
     * @param  \Illuminate\Support\Collection<int, array<int, mixed>>  $rows
     * @return \Illuminate\Support\Collection<int, array<int, mixed>>
     */
    private function explodeSingleColumn($rows)
    {
        // berkas asli: judul di baris pertama, nama kolom lengkap -> apa adanya
        $firstCells = $rows->first() ?? [];
        $firstNormalized = array_map(fn ($v) => $this->normalizeHeading((string) $v), $firstCells);

        if (in_array('deskripsi', $firstNormalized, true)
            && in_array('jatuh_tempo', $firstNormalized, true)
            && in_array('nominal', $firstNormalized, true)) {
            return $rows;
        }

        // sisanya: teks CSV dipaste jadi satu kolom (atau CSV yang kebetulan
        // kepecah sendiri). Pecah ulang tiap baris dengan pemisah yang sama.
        $delimiter = $this->detectDelimiter($rows);
        if ($delimiter === null) {
            return $rows;
        }

        return $rows->map(function ($row) use ($delimiter) {
            $text = implode($delimiter, array_map(
                fn ($v) => $v === null ? '' : (string) $v,
                $row
            ));

            return array_map('trim', explode($delimiter, $text));
        });
    }

    /**
     * Pilih pemisah yang paling masuk akal: yang paling sering muncul, dengan
     * prioritas koma lalu titik koma lalu tab.
     *
     * @param  \Illuminate\Support\Collection<int, array<int, mixed>>  $rows
     */
    private function detectDelimiter($rows): ?string
    {
        $best = null;
        $bestCount = 0;

        foreach (SchedulesImport::DELIMITERS as $delimiter) {
            $count = 0;

            foreach ($rows as $row) {
                $text = (string) (collect($row)->first(fn ($v) => $v !== null && $v !== '') ?? '');
                $count += substr_count($text, $delimiter);
            }

            if ($count > $bestCount) {
                $best = $delimiter;
                $bestCount = $count;
            }
        }

        return $bestCount > 0 ? $best : null;
    }

    /**
     * Tentukan cara membaca isi sel:
     *   - 'columns'  : tiap kolom punya indeks sendiri (berkas normal)
     *   - 'flat'     : satu baris teks mentah, ambil dua kolom terakhir
     *                  sebagai Jatuh Tempo + Nominal, sisanya Deskripsi
     *   - 'position' : berkas multi-kolom tapi judulnya tidak lengkap —
     *                  pakai urutan kolom 1, 2, 3
     *
     * @param  \Illuminate\Support\Collection<int, array<int, mixed>>  $grid
     */
    private function splitPlan($grid): string
    {
        $sample = $grid->first(fn ($row) => count($row) > 1);

        if ($sample !== null) {
            // semua baris punya satu sel saja = teks CSV mentah (satu kolom)
            $allSingleCell = $grid->every(fn ($row) => count($row) <= 1);

            return $allSingleCell ? 'flat' : 'columns';
        }

        return 'position';
    }

    /**
     * @param  array<int, mixed>  $cells
     * @param  array<string, int>  $map
     * @return array{0: string, 1: mixed, 2: mixed}  [deskripsi, jatuh tempo, nominal]
     */
    private function extract(array $cells, array $map, string $split): array
    {
        $nonEmpty = array_values(array_filter($cells, fn ($v) => $v !== null && $v !== ''));

        if ($split === 'flat') {
            // Deskripsi boleh mengandung koma, jadi dua kolom terakhir yang
            // dianggap Jatuh Tempo + Nominal; sisanya digabung jadi Deskripsi.
            $count = count($nonEmpty);

            if ($count === 0) {
                return ['', null, null];
            }

            if ($count === 1) {
                return [trim((string) $nonEmpty[0]), null, null];
            }

            $amount = $nonEmpty[$count - 1];
            $dueDate = $nonEmpty[$count - 2];
            $description = implode(', ', array_map(
                fn ($v) => trim((string) $v),
                array_slice($nonEmpty, 0, $count - 2)
            ));

            return [$description, $dueDate, $amount];
        }

        $index = fn (string $key, int $fallback) => $map[$key] ?? $fallback;
        $at = fn (int $i) => $cells[$i] ?? null;

        return [
            trim((string) $at($index('deskripsi', 0))),
            $at($index('jatuh_tempo', 1)),
            $at($index('nominal', 2)),
        ];
    }

    /**
     * Cari baris judul kolom di antara beberapa baris pertama, lalu petakan
     * tiap kolom logis ke indeks selnya.
     *
     * @param  \Illuminate\Support\Collection<int, array<int, mixed>>  $grid
     * @return array{0: array<string, int>, 1: int}|null  [peta kolom, indeks baris data pertama]
     */
    private function detectHeading($grid): ?array
    {
        $limit = min(5, $grid->count());

        for ($i = 0; $i < $limit; $i++) {
            $cells = $grid[$i];
            $map = [];

            foreach ($cells as $index => $value) {
                $normalized = $this->normalizeHeading((string) $value);
                if ($normalized === '') {
                    continue;
                }

                foreach (SchedulesImport::ALIASES as $key => $aliases) {
                    if (! isset($map[$key]) && in_array($normalized, $aliases, true)) {
                        $map[$key] = $index;
                    }
                }
            }

            // minimal butuh deskripsi + (jatuh tempo atau nominal) supaya
            // tidak salah mengenali baris data sebagai judul kolom
            if (isset($map['deskripsi']) && (isset($map['jatuh_tempo']) || isset($map['nominal']))) {
                return [$map, $i + 1];
            }
        }

        return null;
    }

    private function normalizeHeading(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = str_replace(['(', ')', '.', '/', '-'], ' ', $value);
        $value = preg_replace('/\s+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    /**
     * @return array<int, string>
     */
    private function columnLabels(): array
    {
        return ['Deskripsi', 'Jatuh Tempo', 'Nominal'];
    }

    /**
     * Excel bisa mengubah "12.500" jadi angka desimal 12,5 — kalau dibiarkan,
     * nominal Rp12.500 akan tersimpan sebagai Rp125 (salah 100x). Deteksi
     * bentuk itu supaya bisa dilaporkan sebagai error, bukan disimpan diam-diam.
     */
    private function isDecimalNumber(mixed $value): bool
    {
        if (is_float($value) && floor($value) !== $value) {
            return true;
        }

        if (! is_string($value)) {
            return false;
        }

        return (bool) preg_match('/^\s*[\d.]+,\d+\s*$/', $value);
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel sudah mengubahnya jadi desimal (12.500 -> 12,5): tolak, karena
        // membulatkannya akan menyimpan nominal yang jauh lebih kecil.
        if ($this->isDecimalNumber($value)) {
            return null;
        }

        // buang pemisah ribuan: "5.000" / "5,000" / "Rp 5.000" -> 5000
        $clean = preg_replace('/[^0-9\-]/', '', (string) $value);

        return $clean === '' || $clean === '-' ? null : (int) $clean;
    }

    /**
     * Terima format dd/mm/yyyy (Excel Indonesia), yyyy-mm-dd, atau serial
     * angka Excel supaya berkas buatan sendiri maupun hasil export bisa
     * langsung dipakai.
     */
    private function toDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            try {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                )->toDateString();
            } catch (\Throwable) {
                return null;
            }
        }

        $text = trim((string) $value);

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd M Y', 'j F Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $text);
                if ($date !== false) {
                    return $date->toDateString();
                }
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse($text)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
