<?php

namespace App\Http\Controllers\Admin;

use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    /**
     * Admin: daftar periode kas (mis. Mingguan, Bulanan).
     */
    public function index()
    {
        return Period::latest('id')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'interval_days' => ['required', 'integer', 'min:1'],
        ]);

        $period = Period::create($data);

        return response()->json($period, 201);
    }

    public function update(Request $request, Period $period)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'interval_days' => ['sometimes', 'integer', 'min:1'],
        ]);

        $period->update($data);

        return response()->json($period);
    }

    public function destroy(Period $period)
    {
        $period->delete();

        return response()->noContent();
    }
}