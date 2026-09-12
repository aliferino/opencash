<?php

namespace App\Http\Controllers\Admin;

use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function index()
    {
        return Group::withCount('users')->latest('id')->paginate(15);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $group = Group::create($data);

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
        if ($group->qris_image) {
            Storage::disk('public')->delete($group->qris_image);
        }

        $group->delete();

        return response()->noContent();
    }

    public function uploadQris(Request $request)
    {
        $request->validate([
            'qris_image' => ['required', 'image', 'max:2048'],
        ]);

        $group = $request->user()->group;

        abort_if(! $group, 404, 'Anda belum terdaftar di kelas manapun.');

        if ($group->qris_image) {
            Storage::disk('public')->delete($group->qris_image);
        }

        $path = $request->file('qris_image')->store('qris', 'public');

        $group->update(['qris_image' => $path]);

        return response()->json($group->fresh());
    }

    public function refreshInviteCode(Request $request)
    {
        $group = $request->user()->group;

        abort_if(! $group, 404, 'Anda belum terdaftar di kelas manapun.');

        do {
            $code = Str::upper(Str::random(6));
        } while (\App\Models\Group::where('invite_code', $code)->exists());

        $group->update(['invite_code' => $code]);

        return response()->json(['invite_code' => $code]);
    }
}