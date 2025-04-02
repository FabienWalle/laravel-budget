<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\View\View;

class ParameterController extends Controller
{
    public function index(): View
    {
        $transactions = Transaction::query()
            ->where('user_id', auth()->id())
            ->orderBy('short_description')
            ->orderBy('operation_date', 'desc')
            ->get()
            ->groupBy('short_description');

        return view('parameter.index', [
            'transactions' => $transactions
        ]);
    }
}
