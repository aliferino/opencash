<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

/**
 * Baca berkas Excel/CSV pengeluaran kas (bulk add) jadi baris mentah.
 *
 * Pengeluaran TIDAK punya foto nota saat import massal — nota diisi
 * belakangan kalau perlu. Karena itu `proof_image` tetap nullable.
 */
class ExpensesImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    /** Kolom yang diharapkan ada di berkas. */
    public const COLUMNS = [
        'tanggal' => 'Tanggal',
        'keterangan' => 'Keterangan',
        'nominal' => 'Nominal',
    ];

    /** @var Collection<int, Collection<string, mixed>> */
    public Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    public function collection(Collection $rows): void
    {
        $this->rows = $rows->map(fn ($row) => collect($row)->map(
            fn ($value) => is_string($value) ? trim($value) : $value
        ));
    }
}
