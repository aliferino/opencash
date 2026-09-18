<?php

namespace App\Support;

use App\Models\UserAudit;
use Illuminate\Support\Facades\Auth;

/**
 * Satu pintu untuk menulis `user_audits`.
 *
 * Observer (User/Group) memanggil ini, jadi SEMUA perubahan lewat Eloquent
 * otomatis tercatat — tidak peduli dari controller mana (admin, bendahara,
 * atau artisan). Snapshot nama aktor/subjek/grup disimpan apa adanya supaya
 * log tetap terbaca walau record aslinya sudah dihapus.
 */
class AuditLogger
{
    /**
     * Kolom yang tidak pernah dicatat (noise / tidak relevan untuk audit).
     *
     * @var list<string>
     */
    public const IGNORED_FIELDS = ['remember_token', 'updated_at', 'created_at', 'email_verified_at'];

    /**
     * Kolom yang nilainya disamarkan, tapi perubahannya tetap dicatat.
     *
     * @var list<string>
     */
    public const MASKED_FIELDS = ['password'];

    public const MASK = '••••••••';

    /**
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public static function record(
        string $subjectType,
        ?int $subjectId,
        ?string $subjectName,
        string $action,
        ?array $old = null,
        ?array $new = null,
        ?int $groupId = null,
        ?string $groupName = null,
    ): UserAudit {
        $actor = Auth::user();

        return UserAudit::create([
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'subject_name' => $subjectName,
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'Sistem',
            'group_id' => $groupId,
            'group_name' => $groupName,
            'action' => $action,
            'old_values' => self::sanitize($old),
            'new_values' => self::sanitize($new),
        ]);
    }

    /**
     * Buang kolom noise & samarkan kolom sensitif.
     *
     * @param  array<string, mixed>|null  $values
     * @return array<string, mixed>|null
     */
    public static function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $clean = [];

        foreach ($values as $key => $value) {
            if (in_array($key, self::IGNORED_FIELDS, true)) {
                continue;
            }

            $clean[$key] = in_array($key, self::MASKED_FIELDS, true) && $value !== null
                ? self::MASK
                : $value;
        }

        return $clean === [] ? null : $clean;
    }
}
