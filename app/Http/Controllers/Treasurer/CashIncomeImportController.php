<?php

namespace App\Http\Controllers\Treasurer;

use App\Exports\ImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\IncomesImport;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\User;
use App\Notifications\CashIncomeVerified;
use App\Support\CashLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import & export PEMASUKAN kas (bulk add) — terpisah dari controller CRUD,
 * mengikuti pola yang sama seperti sisi siswa (HistoryController).
 *
 * Alur import:
 *   1. Unduh template (`template()`) supaya nama kolom pasti cocok.
 *   2. Upload berkas ke `store()`.
 *   3. Semua baris divalidasi DULU. Kalau ada satu baris salah, TIDAK ada
 *      yang disimpan (all-or-nothing) dan error dilaporkan per baris —
 *      supaya bendahara tidak menebak-nebak data mana yang sudah masuk.
 *
 * Pembayaran hasil import dicatat sebagai TUNAI + langsung `verified`,
 * sama seperti "Catat Tunai" manual (bendahara sudah pegang uangnya).
 */
class CashIncomeImportController extends Controller
{
    private const COLUMNS = [
        'email_siswa' => 'Email Siswa',
        'deskripsi_tagihan' => 'Deskripsi Tagihan',
        'nominal' => 'Nominal',
        'denda' => 'Denda',
        'tanggal_bayar' => 'Tanggal Bayar',
    ];

    private const EXAMPLE = [
        ['udin@sekolah.id', 'Kas 18 September', 5000, 0, '18/09/2026'],
    ];

    public function template()
    {
        return Excel::download(
            new ImportTemplateExport(
                array_values(self::COLUMNS),
                self::EXAMPLE,
                'Template Pemasukan',
            ),
            'template-import-pemasukan.xlsx'
        );
    }

    public function store(Request $request)
    {
        $treasurer = $request->user();

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:4096'],
        ]);

        $import = new IncomesImport;
        Excel::import($import, $request->file('file'));

        if ($import->rows->isEmpty()) {
            return response()->json([
                'message' => 'Berkas kosong — tidak ada baris data yang bisa dibaca.',
            ], 422);
        }

        $students = User::where('group_id', $treasurer->group_id)
            ->where('role', 'student')
            ->get()
            ->keyBy(fn (User $student) => strtolower($student->email));

        $schedules = CashSchedule::where('group_id', $treasurer->group_id)
            ->get()
            ->keyBy(fn (CashSchedule $schedule) => strtolower(trim($schedule->description)));

        $rows = [];
        $errors = [];
        // dipakai untuk mendeteksi dua baris yang menabrak tagihan yang sama
        $planned = [];

        foreach ($import->rows as $index => $row) {
            $line = $index + 2; // baris 1 = judul kolom
            $email = strtolower(trim((string) $row->get('email_siswa')));
            $description = trim((string) $row->get('deskripsi_tagihan'));
            $amount = $this->toInt($row->get('nominal'));
            $fine = $this->toInt($row->get('denda')) ?? 0;
            $date = $this->toDate($row->get('tanggal_bayar'));

            if ($email === '') {
                $errors[] = "Baris {$line}: email siswa kosong.";
                continue;
            }

            $student = $students->get($email);
            if (! $student) {
                $errors[] = "Baris {$line}: siswa dengan email {$email} tidak ada di kelas ini.";
                continue;
            }

            if ($description === '') {
                $errors[] = "Baris {$line}: deskripsi tagihan kosong.";
                continue;
            }

            $schedule = $schedules->get(strtolower($description));
            if (! $schedule) {
                $errors[] = "Baris {$line}: tagihan \"{$description}\" tidak ditemukan.";
                continue;
            }

            if ($amount === null || $amount < 1) {
                $errors[] = "Baris {$line}: nominal harus angka lebih dari 0.";
                continue;
            }

            if ($date === null) {
                $errors[] = "Baris {$line}: tanggal bayar tidak dikenali (pakai format dd/mm/yyyy atau yyyy-mm-dd).";
                continue;
            }

            $key = $schedule->id.'|'.$student->id;
            $alreadyPlanned = $planned[$key] ?? 0;
            $remaining = CashLedger::remainingFor($schedule, $student->id) - $alreadyPlanned;

            if ($remaining <= 0) {
                $errors[] = "Baris {$line}: tagihan \"{$description}\" untuk {$student->name} sudah lunas.";
                continue;
            }

            if ($amount + $fine > $remaining) {
                $errors[] = "Baris {$line}: nominal melebihi sisa tagihan \"{$description}\" untuk {$student->name} (sisa Rp".number_format($remaining, 0, ',', '.').').';
                continue;
            }

            $planned[$key] = $alreadyPlanned + $amount + $fine;

            $rows[] = [
                'cash_schedule_id' => $schedule->id,
                'student_id' => $student->id,
                'treasurer_id' => $treasurer->id,
                'amount_paid' => $amount,
                'fine_paid' => $fine,
                'income_date' => $date,
                'payment_method' => 'cash',
                'proof_image' => null,
                'notes' => 'Import Excel',
                'status' => 'verified',
            ];
        }

        if ($errors !== []) {
            return response()->json([
                'message' => 'Import dibatalkan, '.count($errors).' baris bermasalah. Tidak ada data yang disimpan.',
                'errors' => array_slice($errors, 0, 20),
                'total_errors' => count($errors),
            ], 422);
        }

        // Simpan satu per satu di dalam transaksi supaya model-nya bisa
        // langsung dipakai untuk notifikasi ke siswa.
        $created = DB::transaction(function () use ($rows) {
            return collect($rows)->map(fn (array $row) => CashIncome::create($row));
        });

        foreach ($created as $income) {
            $income->student?->notify(new CashIncomeVerified($income));
        }

        return response()->json([
            'message' => $created->count().' pembayaran berhasil diimport.',
            'created' => $created->count(),
        ], 201);
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
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
