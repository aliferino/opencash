<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAudit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = UserAudit::query();

        if ($request->filled('subject_type') && in_array($request->string('subject_type')->toString(), ['user', 'group'], true)) {
            $query->where('subject_type', $request->string('subject_type')->toString());
        }

        if ($request->filled('action')) {
            $query->where('action', $request->string('action')->toString());
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->integer('actor_id'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('subject_name', 'like', '%'.$search.'%')
                    ->orWhere('actor_name', 'like', '%'.$search.'%')
                    ->orWhere('group_name', 'like', '%'.$search.'%');
            });
        }

        $query->latest('id');

        if ($request->wantsJson()) {
            return $query->paginate($request->integer('per_page', 15));
        }

        return view('admin.audits.index');
    }
}
