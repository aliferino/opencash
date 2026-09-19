<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

/**
 * Baca berkas Excel/CSV pembayaran kas (bulk add) jadi baris mentah.
 *
 * Kelas ini SENGAJA hanya membaca & merapikan (trim) — validasi dan
 * penyimpanan dilakukan di controller supaya errornya bisa dilaporkan
 * per-baris ("baris 4: nominal melebihi sisa tagihan").
 *
 * Judul kolom dinormalkan otomatis oleh WithHeadingRow, jadi "Email Siswa"
 * maupun "email_siswa" sama-sama terbaca sebagai `email_siswa`.
 */
class IncomesImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    /** Kolom yang diharapkan ada di berkas. */
    public const COLUMNS = [
        'email_siswa' => 'Email Siswa',
        'deskripsi_tagihan' => 'Deskripsi Tagihan',
        'nominal' => 'Nominal',
        'denda' => 'Denda',
        'tanggal_bayar' => 'Tanggal Bayar',
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
