<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('group:id,name');

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->integer('group_id'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->string('search') . '%');
        }

        $query->latest('id');

        if ($request->wantsJson()) {
            return $query->paginate(15);
        }

        return view('admin.users.index');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'role' => ['required', 'in:admin,treasurer,student'],
            'group_id' => ['nullable', 'required_unless:role,admin', 'exists:groups,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'group_id' => $data['role'] === 'admin' ? null : $data['group_id'],
        ]);

        return response()->json($user, 201);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:admin,treasurer,student'],
            'group_id' => ['nullable', 'exists:groups,id'],
        ]);

        if ($data['role'] === 'admin') {
            $data['group_id'] = null;
        }

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->update(['group_id' => null, 'role' => null]);

        return response()->json(['message' => 'User dikeluarkan dari kelas.']);
    }
}