<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserAudit;
use Illuminate\Support\Facades\Auth;

/**
 * Mencatat setiap perubahan pada data users ke tabel user_audits,
 * supaya admin bisa lacak "siapa mengubah apa, kapan" jika ada komplain.
 */
class UserObserver
{
    public function updated(User $user): void
    {
        $changes = $user->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $original = collect($user->getOriginal())
            ->only(array_keys($changes))
            ->toArray();

        UserAudit::create([
            'user_id' => $user->id,
            'updated_by' => Auth::id() ?? $user->id,
            'action' => 'updated',
            'old_values' => $original,
            'new_values' => $changes,
        ]);
    }
}