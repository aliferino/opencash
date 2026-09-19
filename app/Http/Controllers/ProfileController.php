<?php

namespace App\Http\Controllers;

use App\Models\UserAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Edit profil SENDIRI — dipakai bendahara & siswa.
 *
 * Ini satu-satunya halaman yang memang dibagi antara dua role, karena
 * keduanya hanya boleh mengubah akunnya sendiri (tidak ada data milik orang
 * lain yang bisa disentuh). Halaman ber-aksi-tulis yang menyentuh data kelas
 * tetap dipisah per role.
 *
 * Perubahan `name`/`email` otomatis tercatat di `user_audits` lewat
 * `UserObserver` — jangan tulis audit manual di sini.
 */
class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.index', [
            'user' => $request->user(),
            'audits' => UserAudit::where('subject_type', 'user')
                ->where('subject_id', $request->user()->id)
                ->latest('id')
                ->limit(10)
                ->get(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'current_password' => ['nullable', 'required_with:password', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ], [
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'email.unique' => 'Email ini sudah dipakai akun lain.',
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return back()->with('status', empty($data['password'])
            ? 'Profil berhasil diperbarui.'
            : 'Profil dan password berhasil diperbarui.');
    }
}
