<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->integer('group_id'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->string('role'));
        }

        return $query->latest('id')->paginate(15);
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