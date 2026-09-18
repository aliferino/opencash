<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sebelum migrasi ini, config app.timezone masih 'UTC' sementara MySQL server
 * berjalan di zona lokal (WIB). Akibatnya data campur:
 *
 *  - Kolom datetime yang diisi LARAVEL (created_at/updated_at, dst) tersimpan UTC.
 *  - Kolom yang mengandalkan DEFAULT MySQL (mis. user_audits.created_at yang
 *    pakai useCurrent()) tersimpan WIB.
 *
 * Jadi hanya kolom yang diisi Laravel yang perlu digeser +7 jam. Kolom
 * user_audits.created_at sengaja TIDAK ikut digeser karena sudah WIB.
 */
return new class extends Migration
{
    private const OFFSET_HOURS = 7;

    /**
     * Kolom datetime yang diisi Laravel (bukan default MySQL).
     *
     * @var array<string, list<string>>
     */
    private array $columns = [
        'users' => ['created_at', 'updated_at', 'email_verified_at'],
        'groups' => ['created_at', 'updated_at'],
        'group_settings' => ['created_at', 'updated_at'],
        'cash_schedules' => ['created_at', 'updated_at'],
        'cash_incomes' => ['created_at', 'updated_at'],
        'cash_expenses' => ['created_at', 'updated_at'],
        'notifications' => ['created_at', 'updated_at', 'read_at'],
    ];

    public function up(): void
    {
        $this->shift(self::OFFSET_HOURS);
    }

    public function down(): void
    {
        $this->shift(-self::OFFSET_HOURS);
    }

    private function shift(int $hours): void
    {
        $interval = sprintf('%d HOUR', abs($hours));

        foreach ($this->columns as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $existing = array_filter($columns, fn (string $column) => Schema::hasColumn($table, $column));

            if (empty($existing)) {
                continue;
            }

            foreach ($existing as $column) {
                $expression = $hours >= 0
                    ? "DATE_ADD({$column}, INTERVAL {$interval})"
                    : "DATE_SUB({$column}, INTERVAL {$interval})";

                DB::table($table)
                    ->whereNotNull($column)
                    ->update([$column => DB::raw($expression)]);
            }
        }
    }
};
