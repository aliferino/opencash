<?php

namespace App\Observers;

use App\Models\Group;
use App\Support\AuditLogger;

class GroupObserver
{
    public function created(Group $group): void
    {
        AuditLogger::record(
            subjectType: 'group',
            subjectId: $group->id,
            subjectName: $group->name,
            action: 'created',
            new: [
                'name' => $group->name,
                'invite_code' => $group->invite_code,
            ],
            groupId: $group->id,
            groupName: $group->name,
        );
    }

    public function updated(Group $group): void
    {
        $changes = $group->getChanges();
        unset($changes['updated_at']);

        $auditable = array_diff_key($changes, array_flip(AuditLogger::IGNORED_FIELDS));

        if (empty($auditable)) {
            return;
        }

        $original = array_intersect_key($group->getOriginal(), $auditable);

        AuditLogger::record(
            subjectType: 'group',
            subjectId: $group->id,
            subjectName: $group->name,
            action: $this->resolveAction($auditable),
            old: $original,
            new: $auditable,
            groupId: $group->id,
            groupName: $group->name,
        );
    }

    public function deleted(Group $group): void
    {
        AuditLogger::record(
            subjectType: 'group',
            subjectId: $group->id,
            subjectName: $group->name,
            action: 'deleted',
            old: [
                'name' => $group->name,
                'invite_code' => $group->invite_code,
            ],
            groupId: $group->id,
            groupName: $group->name,
        );
    }

    /**
     * @param  array<string, mixed>  $changes
     */
    private function resolveAction(array $changes): string
    {
        return array_key_exists('invite_code', $changes) ? 'invite_code_refreshed' : 'updated';
    }
}
