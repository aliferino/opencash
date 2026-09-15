<?php

namespace App\Http\Controllers\Admin;

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
        if ($request->wantsJson()) {
            $query = Group::withCount('users')->latest('id');

            if ($request->filled('search')) {
                $query->where('name', 'like', '%' . $request->string('search') . '%');
            }

            return $query->paginate(15);
        }

        return view('admin.groups.index');
    }

    public function show(Group $group)
    {
        $group->loadCount('users');

        return view('admin.groups._detail', ['group' => $group]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $group = Group::create([
            'name' => $data['name'],
            'invite_code' => $this->generateInviteCode(),
        ]);

        return response()->json($group, 201);
    }

    public function update(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
        ]);

        $group->update($data);

        return response()->json([
            ...$group->toArray(),
            'updated_at_human' => optional($group->updated_at)->format('d M Y, H:i'),
        ]);
    }

    public function destroy(Group $group)
    {
        $group->delete();

        return response()->noContent();
    }

    public function refreshInviteCode(Group $group)
    {
        $group->update(['invite_code' => $this->generateInviteCode()]);

        return response()->json(['invite_code' => $group->invite_code]);
    }

    public function addMember(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'in:treasurer,student'],
        ]);

        $member = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'group_id' => $group->id,
            'role' => $data['role'],
        ]);

        return response()->json($member, 201);
    }

    public function attachMember(Request $request, Group $group)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);
        
        $user = User::whereNull('group_id')
            ->whereIn('role', ['treasurer', 'student'])
            ->findOrFail($data['user_id']);

        $user->update(['group_id' => $group->id]);

        return response()->json($user);
    }

    private function generateInviteCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Group::where('invite_code', $code)->exists());

        return $code;
    }
}