<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restrukturisasi `user_audits` jadi log audit umum (bukan cuma users).
 *
 * Alasan:
 *  - `user_id` & `updated_by` dulu cascadeOnDelete → audit ikut TERHAPUS kalau
 *    user-nya dihapus, padahal justru itu yang paling perlu dilacak.
 *  - Tidak ada snapshot nama, jadi kalau user dihapus nama di log hilang.
 *  - Tidak bisa mencatat aksi non-user (buat/ubah/hapus grup).
 *
 * Tabel lama di-drop karena isinya cuma noise `remember_token` (efek observer
 * lama yang tidak memfilter kolom itu), jadi tidak ada data berharga yang hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('user_audits');

        Schema::create('user_audits', function (Blueprint $table) {
            $table->id();

            $table->string('subject_type', 20);
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_name')->nullable();

            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();

            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('group_name')->nullable();

            $table->string('action', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['subject_type', 'subject_id']);
            $table->index('actor_id');
            $table->index('group_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_audits');

        Schema::create('user_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('updated_by')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->timestamp('created_at')->useCurrent();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
        });
    }
};
