<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\GroupSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GroupSettingController extends Controller
{
    public function index(Request $request)
    {
        return GroupSetting::where('group_id', $request->user()->group_id)
            ->with('period')
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_id' => ['required', 'exists:periods,id'],
            'cash_amount' => ['required', 'integer', 'min:0'],
            'fine_amount' => ['required', 'integer', 'min:0'],
        ]);

        $setting = GroupSetting::updateOrCreate(
            [
                'group_id' => $request->user()->group_id,
                'period_id' => $data['period_id'],
            ],
            [
                'cash_amount' => $data['cash_amount'],
                'fine_amount' => $data['fine_amount'],
            ],
        );

        return response()->json($setting, 201);
    }

    public function update(Request $request, GroupSetting $groupSetting)
    {
        $this->authorizeOwnership($request, $groupSetting);

        $data = $request->validate([
            'cash_amount' => ['sometimes', 'integer', 'min:0'],
            'fine_amount' => ['sometimes', 'integer', 'min:0'],
        ]);

        $groupSetting->update($data);

        return response()->json($groupSetting);
    }

    public function destroy(Request $request, GroupSetting $groupSetting)
    {
        $this->authorizeOwnership($request, $groupSetting);

        $groupSetting->delete();

        return response()->noContent();
    }

    public function uploadQris(Request $request, GroupSetting $groupSetting)
    {
        $this->authorizeOwnership($request, $groupSetting);

        $request->validate([
            'qris_image' => ['required', 'image', 'max:2048'],
        ]);

        if ($groupSetting->qris_image) {
            Storage::disk('public')->delete($groupSetting->qris_image);
        }

        $path = $request->file('qris_image')->store('qris', 'public');

        $groupSetting->update(['qris_image' => $path]);

        return response()->json($groupSetting->fresh());
    }

    private function authorizeOwnership(Request $request, GroupSetting $groupSetting): void
    {
        abort_unless($groupSetting->group_id === $request->user()->group_id, 403);
    }
}