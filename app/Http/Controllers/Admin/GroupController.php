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

        return response()->json($group);
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

    public function addTreasurer(Request $request, Group $group)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $treasurer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'group_id' => $group->id,
            'role' => 'treasurer',
        ]);

        return response()->json($treasurer, 201);
    }

    private function generateInviteCode(): string
    {
        do {
            $code = Str::upper(Str::random(6));
        } while (Group::where('invite_code', $code)->exists());

        return $code;
    }
}