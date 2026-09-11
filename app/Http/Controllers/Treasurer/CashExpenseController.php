<?php

namespace App\Http\Controllers\Treasurer;

use App\Models\CashExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CashExpenseController extends Controller
{
    /**
     * Semua peran (admin/bendahara/siswa) bisa lihat riwayat pengeluaran
     * beserta foto notanya — transparansi kas kelas.
     */
    public function index(Request $request)
    {
        return CashExpense::where('group_id', $request->user()->group_id)
            ->latest('id')
            ->paginate(20);
    }

    /**
     * Bendahara mencatat pengeluaran kas. Foto nota/struk WAJIB diunggah.
     */
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