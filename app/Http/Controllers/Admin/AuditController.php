<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAudit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = UserAudit::with(['user:id,name,email,group_id', 'updatedBy:id,name']);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('group_id')) {
            $query->whereHas('user', fn ($q) => $q->where('group_id', $request->integer('group_id')));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%'));
        }

        $query->latest('created_at');

        if ($request->wantsJson()) {
            return $query->paginate(20);
        }

        return view('admin.audits.index');
    }
}