<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\PeriodController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Treasurer\CashExpenseController;
use App\Http\Controllers\Treasurer\CashIncomeController;
use App\Http\Controllers\Treasurer\CashScheduleController;
use App\Http\Controllers\Treasurer\DashboardController as TreasurerDashboardController;
use App\Http\Controllers\Treasurer\GroupController as TreasurerGroupController;
use App\Http\Controllers\Treasurer\GroupSettingController;
use App\Http\Controllers\Treasurer\ReportController;
use App\Http\Controllers\Treasurer\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Onboarding: cuma untuk treasurer/student yang belum join kelas.
    // Admin tidak pernah lewat sini (tidak terikat kelas manapun).
    Route::get('onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('onboarding/join', [OnboardingController::class, 'join'])->name('onboarding.join');

    // Notifikasi — dipakai semua role, polling dari lonceng navbar.
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');

    Route::get('dashboard', function () {
        $user = auth()->user();

        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (! $user->group_id) {
            return redirect()->route('onboarding');
        }

        return match ($user->role) {
            'treasurer' => redirect()->route('treasurer.dashboard'),
            'student' => redirect()->route('student.dashboard'),
            default => redirect()->route('onboarding'),
        };
    })->name('dashboard');

    // ===================== ADMIN (global, lintas kelas) =====================
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::get('groups', [AdminGroupController::class, 'index'])->name('groups.index');
        Route::post('groups', [AdminGroupController::class, 'store'])->name('groups.store');
        Route::put('groups/{group}', [AdminGroupController::class, 'update'])->name('groups.update');
        Route::delete('groups/{group}', [AdminGroupController::class, 'destroy'])->name('groups.destroy');
        Route::post('groups/{group}/invite-code/refresh', [AdminGroupController::class, 'refreshInviteCode'])->name('groups.invite-code.refresh');
        Route::post('groups/{group}/treasurers', [AdminGroupController::class, 'addTreasurer'])->name('groups.treasurers.store');

        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::apiResource('periods', PeriodController::class)->except(['show']);

        Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    });

    // ========================= TREASURER (per kelas) =========================
    Route::middleware('role:treasurer')->prefix('treasurer')->name('treasurer.')->group(function () {
        Route::get('/', [TreasurerDashboardController::class, 'index'])->name('dashboard');

        Route::get('group', [TreasurerGroupController::class, 'index'])->name('group.index');
        Route::post('group/invite-code/refresh', [TreasurerGroupController::class, 'refreshInviteCode'])->name('group.invite-code.refresh');

        Route::get('students', [StudentController::class, 'index'])->name('students.index');
        Route::get('members', [StudentController::class, 'members'])->name('members.index');
        Route::post('students', [StudentController::class, 'store'])->name('students.store');
        Route::put('students/{student}', [StudentController::class, 'update'])->name('students.update');
        Route::post('members/{member}/role', [StudentController::class, 'changeRole'])->name('members.change-role');
        Route::delete('students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        Route::get('group-settings', [GroupSettingController::class, 'index'])->name('group-settings.index');
        Route::post('group-settings', [GroupSettingController::class, 'store'])->name('group-settings.store');
        Route::put('group-settings/{groupSetting}', [GroupSettingController::class, 'update'])->name('group-settings.update');
        Route::delete('group-settings/{groupSetting}', [GroupSettingController::class, 'destroy'])->name('group-settings.destroy');
        Route::post('group-settings/{groupSetting}/qris', [GroupSettingController::class, 'uploadQris'])->name('group-settings.qris');

        Route::post('cash-schedules', [CashScheduleController::class, 'store'])->name('cash-schedules.store');
        Route::put('cash-schedules/{cashSchedule}', [CashScheduleController::class, 'update'])->name('cash-schedules.update');
        Route::delete('cash-schedules/{cashSchedule}', [CashScheduleController::class, 'destroy'])->name('cash-schedules.destroy');

        Route::post('cash-incomes/cash', [CashIncomeController::class, 'storeCash'])->name('cash-incomes.store-cash');
        Route::post('cash-incomes/{cashIncome}/verify', [CashIncomeController::class, 'verify'])->name('cash-incomes.verify');

        Route::post('cash-expenses', [CashExpenseController::class, 'store'])->name('cash-expenses.store');
        Route::delete('cash-expenses/{cashExpense}', [CashExpenseController::class, 'destroy'])->name('cash-expenses.destroy');

        Route::get('reports/balance', [ReportController::class, 'balance'])->name('reports.balance');
        Route::get('reports/group', [ReportController::class, 'groupReport'])->name('reports.group');
        Route::get('reports/students/{student}', [ReportController::class, 'studentReport'])->name('reports.student');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });

    // =========================== STUDENT (per kelas) ==========================
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('bills', [PaymentController::class, 'index'])->name('bills.index');
        Route::post('cash-incomes/qris', [CashIncomeController::class, 'storeQris'])->name('cash-incomes.store-qris');
    });

    // ============ SHARED (dibaca oleh treasurer & student di kelas yang sama) ============
    Route::middleware('role:treasurer,student')->group(function () {
        Route::get('cash-schedules', [CashScheduleController::class, 'index'])->name('cash-schedules.index');
        Route::get('cash-expenses', [CashExpenseController::class, 'index'])->name('cash-expenses.index');
        Route::get('cash-incomes', [CashIncomeController::class, 'index'])->name('cash-incomes.index');
    });
});
