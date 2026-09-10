<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_schedule_id')->constrained('cash_schedules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('treasurer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('amount_paid');
            $table->integer('fine_paid')->default(0);
            $table->date('income_date');
            $table->enum('payment_method', ['cash', 'qris']);
            $table->string('proof_image')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_incomes');
    }
};