<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('user_audits');
    }
};