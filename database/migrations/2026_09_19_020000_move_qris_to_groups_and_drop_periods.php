<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Periode dihapus dari desain.
 *
 * Alasan: nominal kas & denda sekarang ditentukan per TAGIHAN
 * (`cash_schedules.amount`), diatur di halaman Jadwal Tagihan — jadi tabel
 * `periods` (referensi global) dan `group_settings` (nominal per periode)
 * tidak lagi punya fungsi. Nama kelas & kode undangan sudah ada di `groups`,
 * diatur dari halaman Grup.
 *
 * Satu-satunya yang masih berguna dari `group_settings` adalah gambar QRIS
 * kelas, jadi kolom itu dipindah ke `groups.qris_image`.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('groups', 'qris_image')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->string('qris_image')->nullable()->after('invite_code');
            });
        }

        // pindahkan QRIS terakhir tiap kelas (kalau tabelnya masih ada)
        if (Schema::hasTable('group_settings')) {
            $latest = DB::table('group_settings')
                ->whereNotNull('qris_image')
                ->orderBy('id')
                ->get()
                ->groupBy('group_id')
                ->map(fn ($rows) => $rows->last()->qris_image);

            foreach ($latest as $groupId => $qrisImage) {
                DB::table('groups')->where('id', $groupId)->update(['qris_image' => $qrisImage]);
            }

            Schema::dropIfExists('group_settings');
        }

        Schema::dropIfExists('periods');
    }

    public function down(): void
    {
        if (! Schema::hasTable('periods')) {
            Schema::create('periods', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedInteger('interval_days');
            });
        }

        if (! Schema::hasTable('group_settings')) {
            Schema::create('group_settings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->cascadeOnDelete();
                $table->foreignId('period_id')->constrained('periods')->cascadeOnDelete();
                $table->integer('cash_amount')->default(0);
                $table->integer('fine_amount')->default(0);
                $table->string('qris_image')->nullable();
                $table->timestamps();
            });
        }
        if (Schema::hasColumn('groups', 'qris_image')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->dropColumn('qris_image');
            });
        }
    }
};
