<?php

namespace Tests\Feature;

use App\Models\CashExpense;
use App\Models\CashIncome;
use App\Models\CashSchedule;
use App\Models\Group;
use App\Models\User;
use App\Support\CashLedger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InstallmentTest extends TestCase
{
    use RefreshDatabase;

    private function makeGroup(): Group
    {
        return Group::create(['name' => 'XII RPL 1', 'invite_code' => 'ABC123']);
    }

    private function makeStudent(Group $group, string $name = 'Udin'): User
    {
        return User::create([
            'name' => $name,
            'email' => strtolower($name).'@sekolah.id',
            'password' => 'password',
            'role' => 'student',
            'group_id' => $group->id,
        ]);
    }

    private function makeTreasurer(Group $group): User
    {
        return User::create([
            'name' => 'Arsi',
            'email' => 'arsi@sekolah.id',
            'password' => 'password',
            'role' => 'treasurer',
            'group_id' => $group->id,
        ]);
    }

    private function makeSchedule(Group $group, int $amount = 5000): CashSchedule
    {
        return CashSchedule::create([
            'group_id' => $group->id,
            'due_date' => '2026-09-18',
            'description' => 'Kas 18 September',
            'amount' => $amount,
        ]);
    }

    /** Contoh dari user: tagihan 5rb, dibayar 3rb → sisa 2rb masih muncul. */
    public function test_partial_payment_leaves_remaining_balance(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($treasurer)
            ->postJson(route('treasurer.cash-incomes.store-cash'), [
                'cash_schedule_id' => $schedule->id,
                'student_id' => $student->id,
                'amount_paid' => 3000,
                'income_date' => '2026-09-18',
            ])
            ->assertCreated();

        $this->assertDatabaseHas('cash_incomes', [
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'amount_paid' => 3000,
            'status' => 'verified',
        ]);

        // Sisa 2000 harus tetap terlihat di halaman Tagihan Saya
        $response = $this->actingAs($student)->get(route('student.bills.index'));
        $response->assertOk();
        $response->assertSee('Kas 18 September');
        $response->assertSee('2.000', false);
        $response->assertSee('Kurang bayar');
    }

    public function test_installments_can_be_paid_multiple_times_until_paid_off(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        foreach ([3000, 2000] as $amount) {
            $this->actingAs($treasurer)
                ->postJson(route('treasurer.cash-incomes.store-cash'), [
                    'cash_schedule_id' => $schedule->id,
                    'student_id' => $student->id,
                    'amount_paid' => $amount,
                    'income_date' => '2026-09-18',
                ])
                ->assertCreated();
        }

        $summary = CashLedger::billSummary($schedule, CashIncome::all());

        $this->assertSame(5000, $summary['paid']);
        $this->assertSame(0, $summary['remaining']);
        $this->assertSame('paid', $summary['status']);
        $this->assertTrue($summary['is_paid']);
    }

    public function test_payment_cannot_exceed_remaining_balance(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($treasurer)
            ->postJson(route('treasurer.cash-incomes.store-cash'), [
                'cash_schedule_id' => $schedule->id,
                'student_id' => $student->id,
                'amount_paid' => 3000,
                'income_date' => '2026-09-18',
            ])
            ->assertCreated();

        $this->actingAs($treasurer)
            ->postJson(route('treasurer.cash-incomes.store-cash'), [
                'cash_schedule_id' => $schedule->id,
                'student_id' => $student->id,
                'amount_paid' => 3000,
                'income_date' => '2026-09-18',
            ])
            ->assertStatus(422);

        $this->assertSame(1, CashIncome::count());
    }

    public function test_payment_after_fully_paid_is_rejected(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($treasurer)->postJson(route('treasurer.cash-incomes.store-cash'), [
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'amount_paid' => 5000,
            'income_date' => '2026-09-18',
        ])->assertCreated();

        $this->actingAs($treasurer)->postJson(route('treasurer.cash-incomes.store-cash'), [
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'amount_paid' => 1000,
            'income_date' => '2026-09-18',
        ])->assertStatus(422);
    }

    /** Pending QRIS ikut dihitung supaya siswa tidak bayar dobel. */
    public function test_pending_qris_counts_toward_remaining(): void
    {
        Storage::fake('public');

        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($student)
            ->post(route('student.cash-incomes.store-qris'), [
                'cash_schedule_id' => $schedule->id,
                'amount_paid' => 3000,
                'proof_image' => UploadedFile::fake()->image('bukti.jpg'),
            ])
            ->assertCreated();

        $summary = CashLedger::billSummary($schedule, CashIncome::all());

        $this->assertSame(0, $summary['paid']);
        $this->assertSame(3000, $summary['pending']);
        $this->assertSame(5000, $summary['remaining']);
        $this->assertSame('pending', $summary['status']);

        // siswa tidak bisa menembak 5000 lagi saat 3000 masih pending
        $this->actingAs($student)
            ->post(route('student.cash-incomes.store-qris'), [
                'cash_schedule_id' => $schedule->id,
                'amount_paid' => 5000,
                'proof_image' => UploadedFile::fake()->image('bukti2.jpg'),
            ])
            ->assertStatus(422);
    }

    public function test_treasurer_can_verify_with_corrected_amount(): void
    {
        Storage::fake('public');

        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($student)->post(route('student.cash-incomes.store-qris'), [
            'cash_schedule_id' => $schedule->id,
            'amount_paid' => 3000,
            'proof_image' => UploadedFile::fake()->image('bukti.jpg'),
        ])->assertCreated();

        $income = CashIncome::firstOrFail();

        // bukti transfer ternyata 4000, bendahara mengoreksi
        $this->actingAs($treasurer)
            ->postJson(route('treasurer.cash-incomes.verify', $income), [
                'status' => 'verified',
                'amount_paid' => 4000,
            ])
            ->assertOk();

        $summary = CashLedger::billSummary($schedule, CashIncome::all());

        $this->assertSame(4000, $summary['paid']);
        $this->assertSame(1000, $summary['remaining']);
    }

    public function test_student_cannot_open_treasurer_schedule_page(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);

        $this->actingAs($student)->get('/cash-schedules')->assertForbidden();
    }

    public function test_student_cannot_create_schedule(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);

        $this->actingAs($student)
            ->postJson(route('treasurer.cash-schedules.store'), [
                'due_date' => '2026-09-18',
                'description' => 'Nakal',
                'amount' => 5000,
            ])
            ->assertForbidden();
    }

    public function test_treasurer_sees_class_progress_per_schedule(): void
    {
        $group = $this->makeGroup();
        $udin = $this->makeStudent($group, 'Udin');
        $sari = $this->makeStudent($group, 'Sari');
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($treasurer)->postJson(route('treasurer.cash-incomes.store-cash'), [
            'cash_schedule_id' => $schedule->id,
            'student_id' => $udin->id,
            'amount_paid' => 3000,
            'income_date' => '2026-09-18',
        ])->assertCreated();

        $this->actingAs($treasurer)->postJson(route('treasurer.cash-incomes.store-cash'), [
            'cash_schedule_id' => $schedule->id,
            'student_id' => $sari->id,
            'amount_paid' => 5000,
            'income_date' => '2026-09-18',
        ])->assertCreated();

        $json = $this->actingAs($treasurer)
            ->getJson(route('cash-schedules.index'))
            ->assertOk()
            ->json();

        $progress = $json[0]['progress'];

        $this->assertSame(10000, $progress['target']);   // 2 siswa x 5000
        $this->assertSame(8000, $progress['paid']);
        $this->assertSame(2000, $progress['remaining']);
        $this->assertSame(1, $progress['paid_students']);
        $this->assertSame(1, $progress['partial_students']);
        $this->assertSame(0, $progress['unpaid_students']);
    }

    public function test_treasurer_remaining_endpoint_returns_correct_remaining(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($treasurer)->postJson(route('treasurer.cash-incomes.store-cash'), [
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'amount_paid' => 3000,
            'income_date' => '2026-09-18',
        ])->assertCreated();

        $this->actingAs($treasurer)
            ->getJson(route('treasurer.cash-incomes.remaining', [
                'cash_schedule_id' => $schedule->id,
                'student_id' => $student->id,
            ]))
            ->assertOk()
            ->assertJson([
                'amount' => 5000,
                'paid' => 3000,
                'remaining' => 2000,
                'status' => 'partial',
            ]);
    }

    public function test_student_history_page_and_exports_work(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($treasurer)->postJson(route('treasurer.cash-incomes.store-cash'), [
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'amount_paid' => 3000,
            'income_date' => '2026-09-18',
        ])->assertCreated();

        $this->actingAs($student)
            ->get(route('student.history.index'))
            ->assertOk()
            ->assertSee('Kas 18 September')
            ->assertSee('Tunai');

        $this->actingAs($student)
            ->get(route('student.history.export.pdf'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($student)
            ->get(route('student.history.export.excel'))
            ->assertOk();
    }

    public function test_rupiah_format_handles_negative_balance(): void
    {
        $this->assertSame('Rp5.000', CashLedger::rupiah(5000));
        $this->assertSame('Rp0', CashLedger::rupiah(0));
        $this->assertSame('−Rp9.000', CashLedger::rupiah(-9000));
    }

    public function test_student_can_see_class_balance_and_expenses(): void
    {
        $group = $this->makeGroup();
        $student = $this->makeStudent($group);
        $treasurer = $this->makeTreasurer($group);
        $schedule = $this->makeSchedule($group, 5000);

        $this->actingAs($treasurer)->postJson(route('treasurer.cash-incomes.store-cash'), [
            'cash_schedule_id' => $schedule->id,
            'student_id' => $student->id,
            'amount_paid' => 5000,
            'income_date' => '2026-09-18',
        ])->assertCreated();

        CashExpense::create([
            'group_id' => $group->id,
            'treasurer_id' => $treasurer->id,
            'amount' => 2000,
            'expense_date' => '2026-09-19',
            'description' => 'Beli spidol',
            'proof_image' => 'proofs/expenses/nota.jpg',
        ]);

        $response = $this->actingAs($student)->get(route('student.cash.index'));

        $response->assertOk();
        $response->assertSee('Beli spidol');
        $response->assertSee('3.000', false); // saldo 5000 - 2000

        $balance = CashLedger::balance($group->id);

        $this->assertSame(5000, $balance['income']);
        $this->assertSame(2000, $balance['expense']);
        $this->assertSame(3000, $balance['balance']);
    }
}
