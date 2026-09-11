<?php

use App\Http\Controllers\CashExpenseController;
use App\Http\Controllers\CashIncomeController;
use App\Http\Controllers\CashScheduleController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupSettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PeriodController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TreasurerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// NOTE: route login/register belum dibuat di sini karena project belum
// pakai auth scaffolding (Breeze/Fortify). Middleware 'auth' di bawah
// mengasumsikan itu sudah ada / akan ditambahkan terpisah.
Route::middleware('auth')->group(function () {

    // ------------------------------------------------------------------
    // Admin — setup master data & assign bendahara
    // ------------------------------------------------------------------
    Route::middleware('role:admin')->group(function () {
        Route::apiResource('groups', GroupController::class)->except(['show']);
        Route::apiResource('periods', PeriodController::class)->except(['show']);
        Route::apiResource('treasurers', TreasurerController::class)->except(['show']);
    });

    // ------------------------------------------------------------------
    // Bendahara — pengaturan kelas, siswa, tagihan, verifikasi, pengeluaran
    // ------------------------------------------------------------------
    Route::middleware('role:treasurer')->group(function () {
        Route::post('/groups/qris', [GroupController::class, 'uploadQris'])->name('groups.qris');

        // FIX: param di-camelCase-kan supaya cocok dengan type-hint
        // GroupSetting $groupSetting di controller (default Laravel
        // pakai snake_case 'group_setting' dan bikin binding gagal).
        Route::apiResource('group-settings', GroupSettingController::class)
            ->parameters(['group-settings' => 'groupSetting'])
            ->except(['show']);

        Route::apiResource('students', StudentController::class)->except(['show']);

        // FIX: sama kasusnya, default 'cash_schedule' -> di-mapping ke
        // 'cashSchedule' supaya cocok dengan CashSchedule $cashSchedule.
        Route::apiResource('cash-schedules', CashScheduleController::class)
            ->parameters(['cash-schedules' => 'cashSchedule'])
            ->except(['show', 'index']);

        Route::post('/cash-incomes/cash', [CashIncomeController::class, 'storeCash'])->name('cash-incomes.cash');
        Route::patch('/cash-incomes/{cashIncome}/verify', [CashIncomeController::class, 'verify'])->name('cash-incomes.verify');

        Route::post('/cash-expenses', [CashExpenseController::class, 'store'])->name('cash-expenses.store');
        Route::delete('/cash-expenses/{cashExpense}', [CashExpenseController::class, 'destroy'])->name('cash-expenses.destroy');

        Route::get('/reports/students/{student}', [ReportController::class, 'studentReport'])->name('reports.student');
        Route::get('/reports/group', [ReportController::class, 'groupReport'])->name('reports.group');
        Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('/reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });

    // ------------------------------------------------------------------
    // Siswa — bayar tagihan via QRIS
    // ------------------------------------------------------------------
    Route::middleware('role:student')->group(function () {
        Route::post('/cash-incomes/qris', [CashIncomeController::class, 'storeQris'])->name('cash-incomes.qris');
    });

    // ------------------------------------------------------------------
    // Dibagikan admin, bendahara, dan siswa (semuanya di-scope ke group_id user)
    // ------------------------------------------------------------------
    Route::middleware('role:admin,treasurer,student')->group(function () {
        Route::get('/cash-schedules', [CashScheduleController::class, 'index'])->name('cash-schedules.index');
        Route::get('/cash-incomes', [CashIncomeController::class, 'index'])->name('cash-incomes.index');
        Route::get('/cash-expenses', [CashExpenseController::class, 'index'])->name('cash-expenses.index');
        Route::get('/reports/balance', [ReportController::class, 'balance'])->name('reports.balance');

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    });
});