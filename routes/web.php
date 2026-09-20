<?php

use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\GroupController as AdminGroupController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\HistoryController as StudentHistoryController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\StudentCashController;
use App\Http\Controllers\Treasurer\CashExpenseController;
use App\Http\Controllers\Treasurer\CashIncomeController;
use App\Http\Controllers\Treasurer\CashScheduleController;
use App\Http\Controllers\Treasurer\CashScheduleImportController;
use App\Http\Controllers\Treasurer\DashboardController as TreasurerDashboardController;
use App\Http\Controllers\Treasurer\GroupController as TreasurerGroupController;
use App\Http\Controllers\Treasurer\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('web.home.index');
})->name('home');

Route::get('/about', function () {
    return view('web.about.index');
})->name('about');

Route::get('/works', function () {
    return view('web.works.index');
})->name('works');

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
    Route::get('onboarding/status', [OnboardingController::class, 'status'])->name('onboarding.status');
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
        Route::get('groups/{group}', [AdminGroupController::class, 'show'])->name('groups.show');
        Route::put('groups/{group}', [AdminGroupController::class, 'update'])->name('groups.update');
        Route::delete('groups/{group}', [AdminGroupController::class, 'destroy'])->name('groups.destroy');
        Route::post('groups/{group}/invite-code/refresh', [AdminGroupController::class, 'refreshInviteCode'])->name('groups.invite-code.refresh');
        Route::post('groups/{group}/members', [AdminGroupController::class, 'addMember'])->name('groups.members.store');
        Route::post('groups/{group}/members/attach', [AdminGroupController::class, 'attachMember'])->name('groups.members.attach');

        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::put('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('audits', [AuditController::class, 'index'])->name('audits.index');
    });

    // ========================= TREASURER (per kelas) =========================
    Route::middleware('role:treasurer')->prefix('treasurer')->name('treasurer.')->group(function () {
        Route::get('/', [TreasurerDashboardController::class, 'index'])->name('dashboard');

        // Grup: bendahara hanya mengelola grupnya sendiri — ubah nama, refresh kode
        // undangan, kelola anggota, dan atur QRIS kelas. TIDAK bisa membuat
        // atau menghapus grup (itu wewenang admin).
        Route::get('group', [TreasurerGroupController::class, 'index'])->name('group.index');
        Route::put('group', [TreasurerGroupController::class, 'update'])->name('group.update');
        Route::post('group/invite-code/refresh', [TreasurerGroupController::class, 'refreshInviteCode'])->name('group.invite-code.refresh');

        Route::get('group/members', [TreasurerGroupController::class, 'members'])->name('group.members.index');
        Route::post('group/members', [TreasurerGroupController::class, 'storeMember'])->name('group.members.store');
        Route::put('group/members/{student}', [TreasurerGroupController::class, 'updateMember'])->name('group.members.update');
        Route::post('group/members/{member}/role', [TreasurerGroupController::class, 'changeRole'])->name('group.members.change-role');
        Route::delete('group/members/{student}', [TreasurerGroupController::class, 'destroyMember'])->name('group.members.destroy');

        Route::post('cash-schedules', [CashScheduleController::class, 'store'])->name('cash-schedules.store');
        Route::put('cash-schedules/{cashSchedule}', [CashScheduleController::class, 'update'])->name('cash-schedules.update');
        Route::delete('cash-schedules/{cashSchedule}', [CashScheduleController::class, 'destroy'])->name('cash-schedules.destroy');
        Route::post('cash-schedules/import', [CashScheduleImportController::class, 'store'])->name('cash-schedules.import.store');

        Route::post('cash-incomes/cash', [CashIncomeController::class, 'storeCash'])->name('cash-incomes.store-cash');
        Route::get('cash-incomes/remaining', [CashIncomeController::class, 'remaining'])->name('cash-incomes.remaining');
        Route::post('cash-incomes/{cashIncome}/verify', [CashIncomeController::class, 'verify'])->name('cash-incomes.verify');

        Route::post('cash-expenses', [CashExpenseController::class, 'store'])->name('cash-expenses.store');
        Route::delete('cash-expenses/{cashExpense}', [CashExpenseController::class, 'destroy'])->name('cash-expenses.destroy');

        // Laporan: satu halaman (ringkasan + rincian pemasukan/pengeluaran),
        // endpoint JSON dipakai untuk memuat rincian secara async.
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/balance', [ReportController::class, 'balance'])->name('reports.balance');
        Route::get('reports/incomes', [ReportController::class, 'incomes'])->name('reports.incomes');
        Route::get('reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
        Route::get('reports/group', [ReportController::class, 'groupReport'])->name('reports.group');
        Route::get('reports/students/{student}', [ReportController::class, 'studentReport'])->name('reports.student');
        Route::get('reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
        Route::get('reports/export/excel', [ReportController::class, 'exportExcel'])->name('reports.export.excel');
    });

    // =========================== STUDENT (per kelas) ==========================
    // Siswa hanya READ: tagihan miliknya, riwayat pembayarannya, dan kondisi
    // kas kelas. Tidak ada route CRUD tagihan di sini — itu wewenang bendahara.
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('bills', [PaymentController::class, 'index'])->name('bills.index');
        Route::get('cash', [StudentCashController::class, 'index'])->name('cash.index');
        Route::get('history', [StudentHistoryController::class, 'index'])->name('history.index');
        Route::get('history/export/pdf', [StudentHistoryController::class, 'exportPdf'])->name('history.export.pdf');
        Route::get('history/export/excel', [StudentHistoryController::class, 'exportExcel'])->name('history.export.excel');
        Route::post('cash-incomes/qris', [CashIncomeController::class, 'storeQris'])->name('cash-incomes.store-qris');
    });

    // Profil sendiri — bendahara & siswa (hanya menyentuh akun sendiri).
    Route::middleware('role:treasurer,student')->group(function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    });

    // ============ SHARED endpoint JSON — HANYA bendahara ============
    // Sebelumnya grup ini `role:treasurer,student`, jadi siswa bisa membuka
    // halaman CRUD Jadwal Tagihan (lengkap dengan tombol tambah/edit/hapus).
    // Sekarang dikunci ke bendahara; siswa punya halaman sendiri di atas.
    Route::middleware('role:treasurer')->group(function () {
        Route::get('cash-schedules', [CashScheduleController::class, 'index'])->name('cash-schedules.index');
        Route::get('cash-expenses', [CashExpenseController::class, 'index'])->name('cash-expenses.index');
        Route::get('cash-incomes', [CashIncomeController::class, 'index'])->name('cash-incomes.index');
    });
});