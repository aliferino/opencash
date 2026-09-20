<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->group_id) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.index');
    }

    public function join(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invite_code' => ['required', 'string'],
        ]);

        $code = $this->normalizeCode($data['invite_code']);

        $group = Group::where('invite_code', $code)->first();

        if (! $group) {
            // Sengaja TIDAK pakai back(): halaman onboarding memanggil
            // /onboarding/status berkala, sehingga "halaman sebelumnya" bisa
            // berupa endpoint JSON. Arahkan eksplisit ke form onboarding.
            return redirect()
                ->route('onboarding')
                ->withErrors([
                    'invite_code' => 'Kode undangan tidak dikenali. Periksa lagi atau minta kode baru ke bendahara kelasmu.',
                ])
                ->withInput();
        }

        $request->user()->update([
            'group_id' => $group->id,
            'role' => 'student',
        ]);

        return redirect()->route('dashboard');
    }

    /**
     * Dipakai polling di halaman onboarding: memberi tahu apakah user sudah
     * dimasukkan ke kelas (lewat kode undangan atau ditambahkan admin/bendahara).
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return response()->json([
                'joined' => true,
                'group' => null,
                'redirect' => route('dashboard'),
            ]);
        }

        // Query langsung (bukan relasi ter-cache) supaya status selalu segar
        // walau model yang sama dipakai berulang dalam satu proses.
        $groupName = $user->group()->value('name');

        return response()->json([
            'joined' => $groupName !== null,
            'group' => $groupName,
            'redirect' => route('dashboard'),
        ]);
    }

    private function normalizeCode(string $value): string
    {
        return Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? '');
    }
}
