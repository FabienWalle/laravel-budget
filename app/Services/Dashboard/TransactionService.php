<?php

namespace App\Services\Dashboard;
use App\Models\Transaction;
use Illuminate\Support\Collection;

class TransactionService
{
    public function getAvailableYears(int $userId): Collection
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->selectRaw('EXTRACT(YEAR FROM operation_date) as year')
            ->distinct()
            ->orderBy('year')
            ->pluck('year')
            ->map(fn($year) => (int)$year);
    }

    public function getAvailableMonths(int $userId, int $year): Collection
    {
        return Transaction::query()
            ->where('user_id', $userId)
            ->whereYear('operation_date', $year)
            ->selectRaw('EXTRACT(MONTH FROM operation_date) as month')
            ->distinct()
            ->orderBy('month')
            ->pluck('month')
            ->map(fn($month) => (int)$month);
    }

    public function getFilteredTransactions(int $userId, string $filterType, ?int $year = null, ?int $month = null): Collection
    {
        $query = Transaction::query()
            ->where('amount', '<', 0)
            ->where('user_id', $userId);

        if ($filterType === 'year' && $year) {
            $query->whereYear('operation_date', $year);
        } elseif ($filterType === 'month' && $year && $month) {
            $query->whereYear('operation_date', $year)
                ->whereMonth('operation_date', $month);
        }

        return $query
            ->selectRaw('category, sum(abs(amount)) as total_amount_cents')
            ->groupBy('category')
            ->orderByDesc('total_amount_cents')
            ->get();
    }
}
