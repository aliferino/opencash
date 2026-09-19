<?php

namespace App\Http\Controllers\Treasurer;

use App\Exports\ImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ExpensesImport;
use App\Models\CashExpense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Import & export PENGELUARAN kas (bulk add) — pola sama seperti pemasukan.
 *
 * Pengeluaran hasil import tidak punya foto nota (import massal tidak bawa
 * gambar). Kalau nota tetap dibutuhkan, catat satu-satu lewat tombol
 * "+ Pengeluaran" yang mewajibkan upload nota.
 */
class CashExpenseImportController extends Controller
{
    private const COLUMNS = [
        'tanggal' => 'Tanggal',
        'keterangan' => 'Keterangan',
        'nominal' => 'Nominal',
    ];

    private const EXAMPLE = [
        ['18/09/2026', 'Beli spidol papan tulis', 2000],
    ];

    public function template()
    {
        return Excel::download(
            new ImportTemplateExport(
                array_values(self::COLUMNS),
                self::EXAMPLE,
                'Template Pengeluaran',
            ),
            'template-import-pengeluaran.xlsx'
        );
    }

    public function store(Request $request)
    {
        $treasurer = $request->user();

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:4096'],
        ]);

        $import = new ExpensesImport;
        Excel::import($import, $request->file('file'));

        if ($import->rows->isEmpty()) {
            return response()->json([
                'message' => 'Berkas kosong — tidak ada baris data yang bisa dibaca.',
            ], 422);
        }

        $rows = [];
        $errors = [];

        foreach ($import->rows as $index => $row) {
            $line = $index + 2;
            $date = $this->toDate($row->get('tanggal'));
            $description = trim((string) $row->get('keterangan'));
            $amount = $this->toInt($row->get('nominal'));

            if ($description === '') {
                $errors[] = "Baris {$line}: keterangan kosong.";
                continue;
            }

            if ($amount === null || $amount < 1) {
                $errors[] = "Baris {$line}: nominal harus angka lebih dari 0.";
                continue;
            }

            if ($date === null) {
                $errors[] = "Baris {$line}: tanggal tidak dikenali (pakai format dd/mm/yyyy atau yyyy-mm-dd).";
                continue;
            }

            $rows[] = [
                'group_id' => $treasurer->group_id,
                'treasurer_id' => $treasurer->id,
                'amount' => $amount,
                'expense_date' => $date,
                'description' => $description,
                'proof_image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($errors !== []) {
            return response()->json([
                'message' => 'Import dibatalkan, '.count($errors).' baris bermasalah. Tidak ada data yang disimpan.',
                'errors' => array_slice($errors, 0, 20),
                'total_errors' => count($errors),
            ], 422);
        }

        DB::transaction(fn () => CashExpense::insert($rows));

        return response()->json([
            'message' => count($rows).' pengeluaran berhasil diimport.',
            'created' => count($rows),
        ], 201);
    }

    private function toInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $clean = preg_replace('/[^0-9\-]/', '', (string) $value);

        return $clean === '' || $clean === '-' ? null : (int) $clean;
    }

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
