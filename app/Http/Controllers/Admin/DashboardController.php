<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\User;
use App\Models\UserAudit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $totalGroups = Group::count();

        $usersByRole = User::whereNotNull('role')
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $recentGroups = Group::withCount('users')->latest('id')->limit(5)->get();

        $recentAudits = UserAudit::with(['user:id,name,group_id', 'updatedBy:id,name'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('admin.index', [
            'totalGroups' => $totalGroups,
            'usersByRole' => $usersByRole,
            'recentGroups' => $recentGroups,
            'recentAudits' => $recentAudits,
        ]);
    }
}