<?php

namespace App\Observers;

use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    public function created(User $user): void
    {
        // Registrasi publik (`Auth\RegisterController`) membuat user dengan
        // role & group_id NULL, dan `User::create()` dipanggil SEBELUM
        // `Auth::login()` — jadi tidak bisa dideteksi dari Auth::user().
        // Semua jalur lain (admin/bendahara) selalu mengisi role eksplisit.
        $isSelfRegistration = $user->role === null;

        AuditLogger::record(
            subjectType: 'user',
            subjectId: $user->id,
            subjectName: $user->name,
            action: $isSelfRegistration ? 'registered' : 'created',
            new: $this->snapshot($user, includePassword: true),
            groupId: $user->group_id,
            groupName: $user->group?->name,
        );
    }

    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        unset($changes['updated_at']);

        $auditable = array_diff_key($changes, array_flip(AuditLogger::IGNORED_FIELDS));

        if (empty($auditable)) {
            return;
        }

        $original = array_intersect_key($user->getOriginal(), $auditable);

        $action = $this->resolveAction($auditable, $original);

        AuditLogger::record(
            subjectType: 'user',
            subjectId: $user->id,
            subjectName: $user->name,
            action: $action,
            old: $original,
            new: $auditable,
            groupId: $user->group_id,
            groupName: $user->group?->name,
        );
    }

    public function deleted(User $user): void
    {
        AuditLogger::record(
            subjectType: 'user',
            subjectId: $user->id,
            subjectName: $user->name,
            action: 'deleted',
            old: $this->snapshot($user),
            groupId: $user->group_id,
            groupName: $user->group?->name,
        );
    }

    /**
     * @param  array<string, mixed>  $changes
     * @param  array<string, mixed>  $original
     */
    private function resolveAction(array $changes, array $original): string
    {
        if (array_key_exists('role', $changes)) {
            return $original['role'] === null ? 'joined' : 'role_changed';
        }

        if (array_key_exists('group_id', $changes) && $changes['group_id'] !== null) {
            return $original['group_id'] === null ? 'joined' : 'group_changed';
        }

        return 'updated';
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(User $user, bool $includePassword = false): array
    {
        $values = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'group_id' => $user->group_id,
        ];

        if ($includePassword) {
            $values['password'] = $user->password;
        }

        return AuditLogger::sanitize($values) ?? [];
    }
}
