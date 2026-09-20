<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;

/**
 * Baca berkas Excel/CSV jadwal tagihan (bulk add) jadi baris mentah.
 *
 * SENGAJA TIDAK memakai `WithHeadingRow`: fitur itu menggabungkan seluruh
 * judul kolom jadi satu kunci kalau berkasnya cuma punya SATU kolom berisi
 * teks CSV (mis. hasil "Salin contoh" yang ditempel ke Excel jadi
 * `deskripsijatuh_temponominal`), sehingga `get('deskripsi')` mengembalikan
 * null dan semua baris dilaporkan "deskripsi kosong".
 *
 * Pemisah CSV juga DIPAKSA lewat `getCsvSettings()` dengan nilai yang
 * dideteksi lebih dulu di controller. Kalau dibiarkan, pembaca CSV menebak
 * sendiri dan pernah salah menebak SPASI sebagai pemisah waktu berkasnya
 * memuat baris kosong — akibatnya "Kas A,18/12/2026,5000" terbaca jadi
 * ["Kas", "A,18/12/2026,5000"] dan tanggalnya tidak dikenali.
 *
 * Kelas ini hanya MEMBACA & merapikan; validasi dan penyimpanan ada di
 * controller supaya error bisa dilaporkan per-baris ("Baris 4: ...").
 */
class SchedulesImport implements SkipsEmptyRows, ToCollection, WithCustomCsvSettings
{
    /** Nama kolom yang dibutuhkan, beserta sinonim yang diterima. */
    public const ALIASES = [
        'deskripsi' => ['deskripsi', 'description', 'keterangan', 'tagihan', 'nama_tagihan'],
        'jatuh_tempo' => ['jatuh_tempo', 'tanggal_jatuh_tempo', 'due_date', 'tanggal', 'tenggat', 'tenggat_waktu'],
        'nominal' => ['nominal', 'jumlah', 'amount', 'total', 'nominal_rp', 'biaya'],
    ];

    /** Pemisah yang dicoba saat mendeteksi format CSV. */
    public const DELIMITERS = [',', ';', "\t"];

    /** @var Collection<int, array<int, mixed>> */
    public Collection $rows;

    public function __construct(private readonly string $csvDelimiter = ',')
    {
        $this->rows = collect();
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => $this->csvDelimiter,
            'input_encoding' => 'UTF-8',
        ];
    }

    public function collection(Collection $rows): void
    {
        $this->rows = $rows->map(fn ($row) => collect($row)->map(
            fn ($value) => is_string($value) ? trim($value) : $value
        )->all())->values();
    }
}
