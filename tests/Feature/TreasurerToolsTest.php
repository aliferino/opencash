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

    // ---------------- IMPORT JADWAL TAGIHAN ----------------

    public function test_schedule_import_creates_multiple_schedules(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas Minggu ke-3 Oktober,18/10/2026,5000',
            'Kas Minggu ke-4 Oktober,25/10/2026,5000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('jadwal.csv', $csv),
            ])
            ->assertCreated()
            ->assertJson(['created' => 2]);

        $this->assertSame(2, CashSchedule::count());
        $this->assertDatabaseHas('cash_schedules', [
            'group_id' => $group->id,
            'description' => 'Kas Minggu ke-3 Oktober',
            'due_date' => '2026-10-18 00:00:00',
            'amount' => 5000,
        ]);
    }

    public function test_schedule_import_accepts_thousand_separators_and_iso_dates(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas Awal Semester,2026-11-05,"Rp 12.500"',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('jadwal.csv', $csv),
            ])
            ->assertCreated();

        $this->assertDatabaseHas('cash_schedules', [
            'description' => 'Kas Awal Semester',
            'due_date' => '2026-11-05 00:00:00',
            'amount' => 12500,
        ]);
    }

    public function test_schedule_import_rejects_all_rows_when_any_row_is_invalid(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas Valid,18/10/2026,5000',
            'Kas Rusak,bukan-tanggal,5000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('jadwal.csv', $csv),
            ])
            ->assertStatus(422);

        // all-or-nothing: baris yang valid pun tidak ikut tersimpan
        $this->assertSame(0, CashSchedule::count());
    }

    public function test_schedule_import_detects_duplicate_rows_in_file(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas Oktober,18/10/2026,5000',
            'Kas Oktober,18/10/2026,5000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('jadwal.csv', $csv),
            ])
            ->assertStatus(422);

        $this->assertSame(0, CashSchedule::count());
    }

    public function test_schedule_import_reads_csv_pasted_into_one_column(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        // kejadian nyata: teks CSV dari Google Sheets ditempel ke Excel,
        // semua masuk kolom A saja (satu kolom, dipisah koma)
        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Tes 1,13/12/2026,5000',
            'Tes 2,13/12/2026,5000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('satu-kolom.csv', $csv),
            ])
            ->assertCreated()
            ->assertJson(['created' => 2]);

        $this->assertDatabaseHas('cash_schedules', [
            'group_id' => $group->id,
            'description' => 'Tes 1',
            'due_date' => '2026-12-13 00:00:00',
            'amount' => 5000,
        ]);
    }

    public function test_schedule_import_accepts_semicolon_separated_file(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Deskripsi;Jatuh Tempo;Nominal',
            'Kas Pungutan;16/12/2026;3000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('titik-koma.csv', $csv),
            ])
            ->assertCreated();

        $this->assertDatabaseHas('cash_schedules', ['description' => 'Kas Pungutan', 'amount' => 3000]);
    }

    public function test_schedule_import_accepts_heading_synonyms(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Keterangan,Tanggal,Jumlah',
            'Dana Kebersihan,17/12/2026,4000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('sinonim.csv', $csv),
            ])
            ->assertCreated();

        $this->assertDatabaseHas('cash_schedules', [
            'description' => 'Dana Kebersihan',
            'due_date' => '2026-12-17 00:00:00',
            'amount' => 4000,
        ]);
    }

    public function test_different_bills_may_share_the_same_due_date(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        // dua tagihan BERBEDA di tanggal yang sama itu wajar
        // (mis. kas mingguan + iuran tambahan di hari yang sama)
        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas Mingguan,21/12/2026,5000',
            'Iuran Tambahan,21/12/2026,2500',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('tanggal-sama.csv', $csv),
            ])
            ->assertCreated()
            ->assertJson(['created' => 2]);

        $this->assertSame(2, CashSchedule::where('due_date', '2026-12-21')->count());
    }

    public function test_schedule_import_rejects_decimal_amount_instead_of_storing_wrong_value(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        // Excel mengubah "12.500" jadi desimal 12,5. Kalau dibulatkan, nominal
        // tersimpan Rp125 — salah 100x. Harus ditolak dengan pesan jelas.
        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas Besar,23/12/2026,"12.500"',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('desimal.csv', $csv),
            ])
            ->assertStatus(422);

        $this->assertSame(0, CashSchedule::count());
    }

    public function test_schedule_import_skips_blank_rows_between_data(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas A,18/12/2026,5000',
            '',
            'Kas B,19/12/2026,5000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('baris-kosong.csv', $csv),
            ])
            ->assertCreated()
            ->assertJson(['created' => 2]);
    }

    public function test_schedule_import_fails_clearly_when_heading_is_missing(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $csv = implode("\n", [
            'Tes 1,13/12/2026,5000',
            'Tes 2,13/12/2026,5000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('tanpa-judul.csv', $csv),
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Judul kolom tidak ditemukan. Pastikan ada baris berisi kolom: Deskripsi, Jatuh Tempo, Nominal.');

        $this->assertSame(0, CashSchedule::count());
    }

    public function test_schedule_import_is_scoped_to_own_group(): void
    {
        $mine = $this->group();
        $other = Group::create(['name' => 'XII RPL 2', 'invite_code' => 'ZZZ999']);
        $treasurer = $this->treasurer($mine);

        $csv = implode("\n", [
            'Deskripsi,Jatuh Tempo,Nominal',
            'Kas Oktober,18/10/2026,5000',
        ]);

        $this->actingAs($treasurer)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('jadwal.csv', $csv),
            ])
            ->assertCreated();

        $this->assertSame(1, CashSchedule::where('group_id', $mine->id)->count());
        $this->assertSame(0, CashSchedule::where('group_id', $other->id)->count());
    }

    public function test_student_cannot_import_schedules(): void
    {
        $group = $this->group();
        $student = $this->student($group);

        $this->actingAs($student)
            ->post(route('treasurer.cash-schedules.import.store'), [
                'file' => UploadedFile::fake()->createWithContent('jadwal.csv', "a,b,c\n1,2,3"),
            ])
            ->assertForbidden();
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

    public function test_treasurer_saves_name_and_qris_with_one_submit(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        // satu tombol Simpan: nama + berkas QRIS dalam satu request
        $this->actingAs($treasurer)
            ->post(route('treasurer.group.update'), [
                '_method' => 'PUT',
                'name' => 'XII RPL 1 Revisi',
                'qris_image' => UploadedFile::fake()->image('qris.png'),
            ])
            ->assertOk()
            ->assertJsonPath('name', 'XII RPL 1 Revisi');

        $group->refresh();
        $this->assertSame('XII RPL 1 Revisi', $group->name);
        $this->assertNotNull($group->qris_image);
    }

    public function test_treasurer_can_update_name_without_touching_qris(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        $this->actingAs($treasurer)
            ->put(route('treasurer.group.update'), ['name' => 'Nama Baru'])
            ->assertOk();

        $group->refresh();
        $this->assertSame('Nama Baru', $group->name);
        $this->assertNull($group->qris_image);
    }

    public function test_qris_upload_route_is_gone(): void
    {
        $group = $this->group();
        $treasurer = $this->treasurer($group);

        // QRIS sekarang ikut tombol Simpan di route group.update
        $this->assertFalse(app('router')->has('treasurer.group.qris'));

        $this->actingAs($treasurer)
            ->post('/treasurer/group/qris', ['qris_image' => UploadedFile::fake()->image('qris.png')])
            ->assertNotFound();
    }

    public function test_student_cannot_save_group_info_or_qris(): void
    {
        $group = $this->group();
        $student = $this->student($group);

        $this->actingAs($student)
            ->put(route('treasurer.group.update'), ['name' => 'Hack'])
            ->assertForbidden();

        $this->assertSame('XII RPL 1', $group->refresh()->name);
        $this->assertNull($group->qris_image);
    }

    public function test_student_sees_class_qris_on_bills_page(): void
    {
        $group = $this->group();
        $student = $this->student($group);
        $treasurer = $this->treasurer($group);

        $this->actingAs($treasurer)
            ->post(route('treasurer.group.update'), [
                '_method' => 'PUT',
                'name' => $group->name,
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
