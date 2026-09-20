<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private function group(): Group
    {
        return Group::create(['name' => 'XII RPL 1', 'invite_code' => 'WRQDUB']);
    }

    private function unassigned(string $email = 'baru@sekolah.id'): User
    {
        return User::create([
            'name' => 'Siswa Baru',
            'email' => $email,
            'password' => 'password',
            'role' => null,
            'group_id' => null,
        ]);
    }

    public function test_ungrouped_user_sees_onboarding_page_with_both_paths(): void
    {
        $this->group();
        $user = $this->unassigned();

        $this->actingAs($user)
            ->get(route('onboarding'))
            ->assertOk()
            ->assertSee('Kode undangan')
            ->assertSee('Tunggu ditambahkan admin')
            ->assertSee('Menunggu ditambahkan');
    }

    public function test_user_with_group_is_redirected_away_from_onboarding(): void
    {
        $group = $this->group();

        $student = User::create([
            'name' => 'Udin',
            'email' => 'udin@sekolah.id',
            'password' => 'password',
            'role' => 'student',
            'group_id' => $group->id,
        ]);

        $this->actingAs($student)->get(route('onboarding'))->assertRedirect(route('dashboard'));
    }

    public function test_admin_is_redirected_away_from_onboarding(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@opencash.test',
            'password' => 'password',
            'role' => 'admin',
            'group_id' => null,
        ]);

        $this->actingAs($admin)->get(route('onboarding'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_status_endpoint_reports_pending_then_joined(): void
    {
        $group = $this->group();
        $user = $this->unassigned();

        $this->actingAs($user)
            ->getJson(route('onboarding.status'))
            ->assertOk()
            ->assertJson(['joined' => false, 'group' => null]);

        // disimulasikan ditambahkan manual oleh admin/bendahara
        $user->update(['group_id' => $group->id, 'role' => 'student']);

        $this->actingAs($user)
            ->getJson(route('onboarding.status'))
            ->assertOk()
            ->assertJson([
                'joined' => true,
                'group' => 'XII RPL 1',
                'redirect' => route('dashboard'),
            ]);
    }

    public function test_join_with_valid_invite_code_assigns_student_role(): void
    {
        $group = $this->group();
        $user = $this->unassigned();

        $this->actingAs($user)
            ->post(route('onboarding.join'), ['invite_code' => 'WRQDUB'])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertSame($group->id, $user->group_id);
        $this->assertSame('student', $user->role);
    }

    public function test_join_normalizes_lowercase_and_whitespace(): void
    {
        $group = $this->group();
        $user = $this->unassigned();

        $this->actingAs($user)
            ->post(route('onboarding.join'), ['invite_code' => '  wr qdub '])
            ->assertRedirect(route('dashboard'));

        $this->assertSame($group->id, $user->refresh()->group_id);
    }

    public function test_join_with_invalid_code_returns_to_onboarding_form(): void
    {
        $this->group();
        $user = $this->unassigned();

        $this->actingAs($user)
            ->post(route('onboarding.join'), ['invite_code' => 'SALAH9'])
            ->assertRedirect(route('onboarding'))
            ->assertSessionHasErrors('invite_code');

        $this->assertNull($user->refresh()->group_id);
        $this->assertNull($user->role);
    }

    public function test_invite_code_is_reusable_across_many_students(): void
    {
        $group = $this->group();
        $first = $this->unassigned('satu@sekolah.id');
        $second = $this->unassigned('dua@sekolah.id');

        $this->actingAs($first)->post(route('onboarding.join'), ['invite_code' => 'WRQDUB'])->assertRedirect();
        $this->actingAs($second)->post(route('onboarding.join'), ['invite_code' => 'WRQDUB'])->assertRedirect();

        $this->assertSame($group->id, $first->refresh()->group_id);
        $this->assertSame($group->id, $second->refresh()->group_id);
        $this->assertSame('WRQDUB', $group->refresh()->invite_code);
    }

    public function test_guest_cannot_open_onboarding_or_join(): void
    {
        $this->group();

        $this->get(route('onboarding'))->assertRedirect(route('login'));
        $this->getJson(route('onboarding.status'))->assertUnauthorized();
        $this->post(route('onboarding.join'), ['invite_code' => 'WRQDUB'])->assertRedirect(route('login'));
    }

    public function test_joined_student_lands_on_student_dashboard(): void
    {
        $this->group();
        $user = $this->unassigned();

        $this->actingAs($user)->post(route('onboarding.join'), ['invite_code' => 'WRQDUB']);

        $this->actingAs($user->refresh())
            ->get(route('dashboard'))
            ->assertRedirect(route('student.dashboard'));
    }
}
