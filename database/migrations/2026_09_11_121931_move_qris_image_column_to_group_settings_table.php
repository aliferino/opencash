<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_settings', function (Blueprint $table) {
            $table->string('qris_image')->nullable()->after('fine_amount');
        });

        DB::table('groups')
            ->whereNotNull('qris_image')
            ->select('id', 'qris_image')
            ->orderBy('id')
            ->each(function ($group) {
                DB::table('group_settings')
                    ->where('group_id', $group->id)
                    ->update(['qris_image' => $group->qris_image]);
            });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('qris_image');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('qris_image')->nullable();
        });

        DB::table('group_settings')
            ->whereNotNull('qris_image')
            ->select('group_id', 'qris_image')
            ->orderBy('id')
            ->each(function ($setting) {
                DB::table('groups')
                    ->where('id', $setting->group_id)
                    ->update(['qris_image' => $setting->qris_image]);
            });

        Schema::table('group_settings', function (Blueprint $table) {
            $table->dropColumn('qris_image');
        });
    }
};