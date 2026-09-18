<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $group = $this->groupOf($request);

        $group->loadCount([
            'users',
            'users as students_count' => fn ($q) => $q->where('role', 'student'),
            'users as treasurers_count' => fn ($q) => $q->where('role', 'treasurer'),
            'cashSchedules',
            'cashExpenses',
        ]);

        if ($request->wantsJson()) {
            return response()->json($group);
        }

        return view('treasurer.groups.index', ['group' => $group]);
    }

    public function update(Request $request)
    {
        $group = $this->groupOf($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $group->update($data);

        return response()->json([
            ...$group->toArray(),
            'updated_at_human' => optional($group->updated_at)->format('d M Y, H:i'),
        ]);
    }

    public function refreshInviteCode(Request $request)
    {
        $group = $this->groupOf($request);

        do {
            $code = Str::upper(Str::random(6));
        } while (Group::where('invite_code', $code)->exists());

        $group->update(['invite_code' => $code]);

        return response()->json(['invite_code' => $code]);
    }

    public function members(Request $request)
    {
        $query = User::where('group_id', $request->user()->group_id)
            ->whereIn('role', ['student', 'treasurer']);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%'));
        }

        if ($request->filled('role') && in_array($request->string('role')->toString(), ['student', 'treasurer'], true)) {
            $query->where('role', $request->string('role')->toString());
        }

        return $query->latest('id')->paginate($request->integer('per_page', 15));
    }

    public function storeMember(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'in:student,treasurer'],
        ]);

        $member = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'group_id' => $request->user()->group_id,
            'role' => $data['role'],
        ]);

        return response()->json($member, 201);
    }

    public function updateMember(Request $request, User $student)
    {
        $this->authorizeMember($request, $student);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$student->id],
            'role' => ['sometimes', 'in:student,treasurer'],
        ]);

        if (array_key_exists('role', $data) && $data['role'] !== $student->role) {
            abort_if($student->id === $request->user()->id, 422, 'Tidak bisa mengubah role akun sendiri.');
        }

        $student->update($data);

        return response()->json($student->fresh());
    }

    public function changeRole(Request $request, User $member)
    {
        $treasurer = $request->user();

        abort_unless(
            $member->group_id === $treasurer->group_id && in_array($member->role, ['student', 'treasurer'], true),
            403
        );
        abort_if($member->id === $treasurer->id, 422, 'Tidak bisa mengubah role akun sendiri.');

        $data = $request->validate([
            'role' => ['required', 'in:student,treasurer'],
        ]);

        $member->update(['role' => $data['role']]);

        return response()->json($member->fresh());
    }

    public function destroyMember(Request $request, User $student)
    {
        $this->authorizeMember($request, $student);

        abort_if($student->id === $request->user()->id, 422, 'Tidak bisa mengeluarkan akun sendiri.');

        $student->delete();

        return response()->noContent();
    }

    private function groupOf(Request $request): Group
    {
        $group = $request->user()->group;

        abort_if(! $group, 404, 'Anda belum terdaftar di kelas manapun.');

        return $group;
    }

    private function authorizeMember(Request $request, User $member): void
    {
        abort_unless(
            in_array($member->role, ['student', 'treasurer'], true)
                && $member->group_id === $request->user()->group_id,
            403
        );
    }
}
