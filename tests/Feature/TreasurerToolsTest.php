<?php

namespace Tests\Feature;

use App\Exports\TreasurerReportExport;
use App\Models\CashExpense;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class TreasurerToolsTest extends TestCase
{
    use RefreshDatabase;

    private function group(): Group
    {
        return Group::create(['name' => 'XII RPL 1', 'invite_code' => 'ABC123']);
    }

    private function student(Group $group, string $name = 'Udin', string $email = 'udin@sekolah.id'): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => 'password',
            'role' => 'student',
            'group_id' => $group->id,
        ]);
    }

    private function treasurer(Group $group): User
    {
        return User::create([
            'name' => 'Arsi',
            'email' => 'arsi@sekolah.id',
            'password' => 'password',
            'role' => 'treasurer',
            'group_id' => $group->id,
        ]);
    }

    private function schedule(Group $group, string $description = 'Kas 18 September', int $amount = 5000): CashSchedule
    {
        return CashSchedule::create([
            'group_id' => $group->id,
            'due_date' => '2026-09-18',
            'description' => $description,
            'amount' => $amount,
        ]);
    }

    // ---------------- IMPORT PEMASUKAN ----------------

    public function test_income_import_creates_multiple_payments(): void
    {
        $group = $this->group();
        $udin = $this->student($group, 'Udin', 'udin@sekolah.id');
        $sari = $this->student($group, 'Sari', 'sari@sekolah.id');
        $treasurer = $this->treasurer($group);
        $this->schedule($group);

        $csv = implode("\n", [
            'Email Siswa,Deskripsi Tagihan,Nominal,Denda,Tanggal Bayar',
            'udin@sekolah.id,Kas 18 September,3000,0,18/09/2026',
            'sari@sekolah.id,Kas 18 September,5000,0,18/09/2026',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-incomes.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('pemasukan.csv', $csv),
            ])
            ->assertCreated()
            ->assertJson(['created' => 2]);

        $this->assertSame(2, CashIncome::count());
        $this->assertDatabaseHas('cash_incomes', ['student_id' => $udin->id, 'amount_paid' => 3000, 'status' => 'verified']);
        $this->assertDatabaseHas('cash_incomes', ['student_id' => $sari->id, 'amount_paid' => 5000]);
    }

    public function test_income_import_rejects_all_rows_when_any_row_is_invalid(): void
    {
        $group = $this->group();
        $this->student($group);
        $treasurer = $this->treasurer($group);
        $this->schedule($group);

        $csv = implode("\n", [
            'Email Siswa,Deskripsi Tagihan,Nominal,Denda,Tanggal Bayar',
            'udin@sekolah.id,Kas 18 September,3000,0,18/09/2026',
            'tidak-ada@sekolah.id,Kas 18 September,5000,0,18/09/2026',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-incomes.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('pemasukan.csv', $csv),
            ])
            ->assertStatus(422);

        // all-or-nothing: tidak ada yang tersimpan
        $this->assertSame(0, CashIncome::count());
    }

    public function test_income_import_rejects_amount_over_remaining(): void
    {
        $group = $this->group();
        $this->student($group);
        $treasurer = $this->treasurer($group);
        $this->schedule($group, 'Kas 18 September', 5000);

        $csv = implode("\n", [
            'Email Siswa,Deskripsi Tagihan,Nominal,Denda,Tanggal Bayar',
            'udin@sekolah.id,Kas 18 September,9000,0,18/09/2026',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-incomes.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('pemasukan.csv', $csv),
            ])
            ->assertStatus(422);

        $this->assertSame(0, CashIncome::count());
    }

    public function test_income_import_detects_conflict_between_rows(): void
    {
        $group = $this->group();
        $this->student($group);
        $treasurer = $this->treasurer($group);
        $this->schedule($group, 'Kas 18 September', 5000);

        // dua baris untuk tagihan yang sama, totalnya 6000 > 5000
        $csv = implode("\n", [
            'Email Siswa,Deskripsi Tagihan,Nominal,Denda,Tanggal Bayar',
            'udin@sekolah.id,Kas 18 September,3000,0,18/09/2026',
            'udin@sekolah.id,Kas 18 September,3000,0,18/09/2026',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-incomes.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('pemasukan.csv', $csv),
            ])
            ->assertStatus(422);

        $this->assertSame(0, CashIncome::count());
    }

    public function test_income_import_template_downloads(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $this->actingAs($treasurer)
            ->get(route('treasurer.cash-incomes.import.template'))
            ->assertOk();
    }

    public function test_student_cannot_import(): void
    {
        $group = $this->group();
        $student = $this->student($group);

        $this->actingAs($student)
            ->post(route('treasurer.cash-incomes.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('x.csv', "a,b\n1,2"),
            ])
            ->assertForbidden();
    }

    // ---------------- IMPORT PENGELUARAN ----------------

    public function test_expense_import_creates_multiple_expenses(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Tanggal,Keterangan,Nominal',
            '18/09/2026,Beli spidol,2000',
            '19/09/2026,Iuran kebersihan,15000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-expenses.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('pengeluaran.csv', $csv),
            ])
            ->assertCreated()
            ->assertJson(['created' => 2]);

        $this->assertSame(2, CashExpense::count());
        $this->assertDatabaseHas('cash_expenses', ['description' => 'Beli spidol', 'amount' => 2000]);
    }

    public function test_expense_import_rejects_invalid_row(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Tanggal,Keterangan,Nominal',
            '18/09/2026,,2000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-expenses.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('pengeluaran.csv', $csv),
            ])
            ->assertStatus(422);

        $this->assertSame(0, CashExpense::count());
    }

    public function test_expense_import_template_downloads(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $this->actingAs($treasurer)
            ->get(route('treasurer.cash-expenses.import.template'))
            ->assertOk();
    }

    // ---------------- EXPORT LAPORAN ----------------

    public function test_report_export_respects_scope(): void
    {
        $group = $this->group();
        $student = $this->student($group);
        $treasurer = $this->treasurer($group);
        $schedule = $this->schedule($group);

        CashIncome::create([
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'treasurer_id' => $treasurer->id,
            'amount_paid' => 5000,
            'fine_paid' => 0,
            'income_date' => '2026-09-18',
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);

        CashExpense::create([
            'group_id' => $group->id,
            'treasurer_id' => $treasurer->id,
            'amount' => 2000,
            'expense_date' => '2026-09-19',
            'description' => 'Beli spidol',
            'proof_image' => 'proofs/expenses/nota.jpg',
        ]);

        // `incomes()`/`expenses()` adalah accessor data mentah (tanpa filter
        // scope) — penyaringan terjadi saat baris disusun di array().
        $income = new TreasurerReportExport($group->id, 'income', $group->name);
        $incomeText = collect($income->array())->flatten()->filter()->implode(' ');
        $this->assertStringContainsString('PEMASUKAN', $incomeText);
        $this->assertStringNotContainsString('PENGELUARAN', $incomeText);
        $this->assertStringNotContainsString('Beli spidol', $incomeText);

        $expense = new TreasurerReportExport($group->id, 'expense', $group->name);
        $expenseText = collect($expense->array())->flatten()->filter()->implode(' ');
        $this->assertStringContainsString('PENGELUARAN', $expenseText);
        $this->assertStringNotContainsString('PEMASUKAN', $expenseText);
        $this->assertStringNotContainsString('Udin', $expenseText);

        $all = new TreasurerReportExport($group->id, 'all', $group->name);
        $allText = collect($all->array())->flatten()->filter()->implode(' ');
        $this->assertStringContainsString('PEMASUKAN', $allText);
        $this->assertStringContainsString('PENGELUARAN', $allText);
        $this->assertStringContainsString('RINGKASAN', $allText);
        $this->assertStringContainsString('Saldo Kas', $allText);
    }

    public function test_report_exports_download_for_each_scope(): void
    {
        $group = $this->group();
        $student = $this->student($group);
        $treasurer = $this->treasurer($group);
        $schedule = $this->schedule($group);

        CashIncome::create([
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'treasurer_id' => $treasurer->id,
            'amount_paid' => 5000,
            'fine_paid' => 0,
            'income_date' => '2026-09-18',
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);

        CashExpense::create([
            'group_id' => $group->id,
            'treasurer_id' => $treasurer->id,
            'amount' => 2000,
            'expense_date' => '2026-09-19',
            'description' => 'Beli spidol',
            'proof_image' => 'proofs/expenses/nota.jpg',
        ]);

        foreach (['all', 'income', 'expense'] as $scope) {
            $this->actingAs($treasurer)
                ->get(route('treasurer.reports.export.pdf', ['scope' => $scope]))
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');

            $this->actingAs($treasurer)
                ->get(route('treasurer.reports.export.excel', ['scope' => $scope]))
                ->assertOk();
        }
    }

    public function test_reports_page_renders_without_error(): void
    {
        $group = $this->group();
        $student = $this->student($group);
        $treasurer = $this->treasurer($group);
        $schedule = $this->schedule($group);

        // dulu halaman ini error 500 karena Eloquent Collection::merge()
        CashIncome::create([
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'treasurer_id' => $treasurer->id,
            'amount_paid' => 5000,
            'fine_paid' => 0,
            'income_date' => '2026-09-18',
            'payment_method' => 'cash',
            'status' => 'verified',
        ]);

        CashExpense::create([
            'group_id' => $group->id,
            'treasurer_id' => $treasurer->id,
            'amount' => 2000,
            'expense_date' => '2026-09-19',
            'description' => 'Beli spidol',
            'proof_image' => 'proofs/expenses/nota.jpg',
        ]);

        $this->actingAs($treasurer)
            ->get(route('treasurer.reports.index'))
            ->assertOk()
            ->assertSee('Laporan Kas');
    }

    // ---------------- PROFIL ----------------

    public function test_treasurer_and_student_can_open_profile_page(): void
    {
        $group = $this->group();

        $this->actingAs($this->treasurer($group))->get(route('profile.edit'))->assertOk()->assertSee('Profil Saya');
        $this->actingAs($this->student($group))->get(route('profile.edit'))->assertOk()->assertSee('Profil Saya');
    }

    public function test_admin_cannot_open_profile_page(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@opencash.test',
            'password' => 'password',
            'role' => 'admin',
            'group_id' => null,
        ]);

        $this->actingAs($admin)->get(route('profile.edit'))->assertForbidden();
    }

    public function test_profile_update_changes_name_and_email(): void
    {
        $group = $this->group();
        $student = $this->student($group);

        $this->actingAs($student)
            ->put(route('profile.update'), [
                'name' => 'Udin Baru',
                'email' => 'udin.baru@sekolah.id',
            ])
            ->assertRedirect();

        $student->refresh();
        $this->assertSame('Udin Baru', $student->name);
        $this->assertSame('udin.baru@sekolah.id', $student->email);
    }

    public function test_profile_update_requires_current_password_to_change_password(): void
    {
        $group = $this->group();
        $student = $this->student($group);
        $oldHash = $student->password;

        // password saat ini salah -> ditolak
        $this->actingAs($student)
            ->put(route('profile.update'), [
                'name' => $student->name,
                'email' => $student->email,
                'current_password' => 'salah-banget',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertSessionHasErrors('current_password');

        $this->assertSame($oldHash, $student->refresh()->password);

        // password saat ini benar -> berhasil
        $this->actingAs($student)
            ->put(route('profile.update'), [
                'name' => $student->name,
                'email' => $student->email,
                'current_password' => 'password',
                'password' => 'passwordbaru123',
                'password_confirmation' => 'passwordbaru123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('passwordbaru123', $student->refresh()->password));
    }

    public function test_profile_update_rejects_email_used_by_another_user(): void
    {
        $group = $this->group();
        $udin = $this->student($group, 'Udin', 'udin@sekolah.id');
        $this->student($group, 'Sari', 'sari@sekolah.id');

        $this->actingAs($udin)
            ->put(route('profile.update'), [
                'name' => 'Udin',
                'email' => 'sari@sekolah.id',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_profile_update_keeps_email_when_unchanged(): void
    {
        $group = $this->group();
        $student = $this->student($group);

        $this->actingAs($student)
            ->put(route('profile.update'), [
                'name' => 'Udin Ganti Nama',
                'email' => 'udin@sekolah.id',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('Udin Ganti Nama', $student->refresh()->name);
    }

    // ---------------- QRIS DI HALAMAN GRUP ----------------

    public function test_treasurer_can_upload_class_qris(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $this->actingAs($treasurer)
            ->post(route('treasurer.group.qris'), [
                'qris_image' => UploadedFile::fake()->image('qris.png'),
            ])
            ->assertOk();

        $this->assertNotNull($group->refresh()->qris_image);
    }

    public function test_student_sees_class_qris_on_bills_page(): void
    {
        $group = $this->group();
        $student = $this->student($group);
        $treasurer = $this->treasurer($group);

        $this->actingAs($treasurer)
            ->post(route('treasurer.group.qris'), [
                'qris_image' => UploadedFile::fake()->image('qris.png'),
            ])
            ->assertOk();

        $this->actingAs($student)
            ->get(route('student.bills.index'))
            ->assertOk()
            ->assertSee('QRIS');
    }

    // ---------------- HALAMAN YANG DIHAPUS ----------------

    public function test_period_and_group_settings_pages_are_gone(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $this->actingAs($treasurer)->get('/treasurer/group-settings')->assertNotFound();
        $this->actingAs($treasurer)->get('/treasurer/periods')->assertNotFound();

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@opencash.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->get('/admin/periods')->assertNotFound();
    }

    public function test_schedules_detail_data_is_exposed_for_modal(): void
    {
        $group = $this->group();
        $this->student($group);
        $treasurer = $this->treasurer($group);
        $this->schedule($group);

        $json = $this->actingAs($treasurer)
            ->getJson(route('cash-schedules.index'))
            ->assertOk()
            ->json();

        $this->assertArrayHasKey('progress', $json[0]);
        $this->assertArrayHasKey('students', $json[0]['progress']);
        $this->assertSame('Udin', $json[0]['progress']['students'][0]['student_name']);
    }
}
