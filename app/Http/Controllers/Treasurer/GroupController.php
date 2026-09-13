<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupController extends Controller
{
    public function index(Request $request)
    {
        $group = $request->user()->group;

        abort_if(! $group, 404, 'Anda belum terdaftar di kelas manapun.');

        return response()->json($group);
    }

    public function refreshInviteCode(Request $request)
    {
        $group = $request->user()->group;

        abort_if(! $group, 404, 'Anda belum terdaftar di kelas manapun.');

        do {
            $code = Str::upper(Str::random(6));
        } while (Group::where('invite_code', $code)->exists());

        $group->update(['invite_code' => $code]);

        return response()->json(['invite_code' => $code]);
    }
}