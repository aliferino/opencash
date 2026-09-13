<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        return User::where('group_id', $request->user()->group_id)
            ->where('role', 'student')
            ->latest('id')
            ->paginate(20);
    }

    public function members(Request $request)
    {
        return User::where('group_id', $request->user()->group_id)
            ->whereIn('role', ['student', 'treasurer'])
            ->latest('id')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', Password::defaults()],
        ]);

        $student = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'group_id' => $request->user()->group_id,
            'role' => 'student',
        ]);

        return response()->json($student, 201);
    }

    public function update(Request $request, User $student)
    {
        $this->authorizeOwnership($request, $student);

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'email', 'unique:users,email,'.$student->id],
        ]);

        $student->update($data);

        return response()->json($student);
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

    public function destroy(Request $request, User $student)
    {
        $this->authorizeOwnership($request, $student);

        $student->delete();

        return response()->noContent();
    }

    private function authorizeOwnership(Request $request, User $student): void
    {
        abort_unless(
            $student->role === 'student' && $student->group_id === $request->user()->group_id,
            403
        );
    }
}