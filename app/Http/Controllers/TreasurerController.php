<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class TreasurerController extends Controller
{
    /**
     * Admin: lihat semua akun bendahara beserta kelasnya.
     */
    public function index()
    {
        return User::where('role', 'treasurer')->with('group')->latest('id')->paginate(15);
    }

    /**
     * Admin: buat akun bendahara dan langsung tugaskan ke sebuah kelas.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
            'group_id' => ['required', 'exists:groups,id'],
        ]);

        $treasurer = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'group_id' => $data['group_id'],
            'role' => 'treasurer',
        ]);

        return response()->json($treasurer, 201);
    }

    public function update(Request $request, User $treasurer)
    {
        abort_unless($treasurer->role === 'treasurer', 404);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'group_id' => ['sometimes', 'exists:groups,id'],
        ]);

        $treasurer->update($data);

        return response()->json($treasurer);
    }

    public function destroy(User $treasurer)
    {
        abort_unless($treasurer->role === 'treasurer', 404);

        $treasurer->delete();

        return response()->noContent();
    }
}