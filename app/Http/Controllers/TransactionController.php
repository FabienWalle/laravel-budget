<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(): View
    {
        $transactions = Transaction::query()
            ->orderBy('operation_date', 'DESC')
            ->paginate(10);

        return view('transactions.dashboard', compact('transactions'));
    }

    public function create(): View
    {
        return view('transactions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:income,expense'],
            'date' => ['required', 'date']
        ]);

        Transaction::class->create($validated);

        return redirect('/transactions')
            ->with('success', 'Transaction créée avec succès');
    }

    public function show(Transaction $transaction): View
    {
        return view('transactions.show', [
            'transaction' => $transaction
        ]);
    }

    public function edit(Transaction $transaction): View
    {
        return view('transactions.edit', [
            'transaction' => $transaction
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'type' => ['required', 'in:income,expense'],
            'date' => ['required', 'date']
        ]);

        $transaction->update($validated);

        return redirect('/transactions/' . $transaction)
            ->with('success', 'Transaction mise à jour avec succès');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $transaction->delete();

        return redirect('/transactions')
            ->with('success', 'Transaction supprimée avec succès');
    }
}
