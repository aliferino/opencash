<?php

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (auth()->user()->group_id) {
            return redirect()->route('dashboard');
        }

        return view('onboarding.waiting-group');
    }

    public function createGroup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $group = Group::create($data);

        $request->user()->update([
            'group_id' => $group->id,
            'role' => 'admin',
        ]);

        return redirect()->route('dashboard');
    }

    public function join(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'invite_code' => ['required', 'string'],
        ]);

        $code = Str::upper($data['invite_code']);

        $group = Group::where('invite_code', $code)->first();

        if (! $group) {
            return back()->withErrors([
                'invite_code' => 'Kode tidak valid atau sudah kadaluarsa.',
            ]);
        }

        $request->user()->update([
            'group_id' => $group->id,
            'role' => 'student',
        ]);

        $group->update(['invite_code' => null]);

        return redirect()->route('dashboard');
    }
}