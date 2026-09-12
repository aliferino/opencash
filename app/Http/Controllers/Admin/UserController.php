<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        return User::where('group_id', $request->user()->group_id)
            ->latest('id')
            ->paginate(15);
    }

    public function update(Request $request, User $user)
    {
        abort_unless($user->group_id === $request->user()->group_id, 403);

        $data = $request->validate([
            'role' => ['required', 'in:admin,treasurer,student'],
        ]);

        $user->update($data);

        return response()->json($user);
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($user->group_id === $request->user()->group_id, 403);

        $user->update(['group_id' => null, 'role' => null]);

        return response()->json(['message' => 'User dikeluarkan dari grup.']);
    }
}