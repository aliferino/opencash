<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Models\CashExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CashExpenseController extends Controller
{
    public function index(Request $request)
    {
        $groupId = $request->user()->group_id;

        $query = CashExpense::with('treasurer')->where('group_id', $groupId);

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%'.$search.'%')
                    ->orWhereHas('treasurer', fn ($treasurer) => $treasurer->where('name', 'like', '%'.$search.'%'));
            });
        }

        $query->latest('expense_date')->latest('id');

        if (! $request->wantsJson() && $request->user()->isTreasurer()) {
            $summary = [
                'count' => CashExpense::where('group_id', $groupId)->count(),
                'total' => (int) CashExpense::where('group_id', $groupId)->sum('amount'),
            ];

            return view('treasurer.expenses.index', compact('summary'));
        }

        return $query->paginate($request->integer('per_page', 15));
    }

    public function store(Request $request)
    {
        $treasurer = $request->user();
        abort_unless($treasurer->isTreasurer(), 403);

        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
            'expense_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'proof_image' => ['required', 'image', 'max:2048'],
        ]);

        $path = $request->file('proof_image')->store('proofs/expenses', 'public');

        $expense = CashExpense::create([
            'group_id' => $treasurer->group_id,
            'treasurer_id' => $treasurer->id,
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            'description' => $data['description'],
            'proof_image' => $path,
        ]);

        return response()->json($expense, 201);
    }

    public function destroy(Request $request, CashExpense $cashExpense)
    {
        abort_unless($cashExpense->group_id === $request->user()->group_id, 403);

        if ($cashExpense->proof_image) {
            Storage::disk('public')->delete($cashExpense->proof_image);
        }

        $cashExpense->delete();

        return response()->noContent();
    }
}
