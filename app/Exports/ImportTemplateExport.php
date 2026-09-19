<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template import (judul kolom + baris contoh) supaya bendahara tidak salah
 * nama kolom. Dipakai tombol "Unduh Template" di modal import.
 *
 * Baris contoh ditulis miring & abu-abu, jadi jelas itu contoh dan bukan data
 * yang ikut terimport (baris contoh punya email/keterangan dummy yang tidak
 * akan cocok, jadi kalau ikut terkirim akan ditolak validasi).
 */
class ImportTemplateExport implements FromArray, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * @param  array<int, string>  $headings
     * @param  array<int, array<int, mixed>>  $examples
     */
    public function __construct(
        private readonly array $headings,
        private readonly array $examples = [],
        private readonly string $sheetTitle = 'Template',
    ) {}

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function array(): array
    {
        return $this->examples;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => ['font' => ['bold' => true]],
        ];

        // baris contoh dibuat miring supaya beda dari judul kolom
        foreach (array_keys($this->examples) as $index) {
            $styles[$index + 2] = ['font' => ['italic' => true, 'color' => ['rgb' => '6B7280']]];
        }

        return $styles;
    }
}
