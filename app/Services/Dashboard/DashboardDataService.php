<?php

namespace App\Services\Dashboard;

use App\DataTransferObjects\DashboardData;

class DashboardDataService
{
    public function __construct(
        private readonly TransactionService $transactionService
    ) {}

    public function getDashboardData(int $userId, array $filters): DashboardData
    {
        $availableYears = $this->transactionService->getAvailableYears($userId);

        $availableMonths = [];
        foreach ($availableYears as $year) {
            $availableMonths[$year] = $this->transactionService->getAvailableMonths($userId, $year);
        }

        $transactions = $this->transactionService->getFilteredTransactions(
            $userId,
            $filters['filterType'],
            $filters['year'],
            $filters['month']
        );

        $totalCents = $transactions->sum('total_amount_cents');

        $labels = [];
        $data = [];
        $colors = [];
        $tooltips = [];

        foreach ($transactions as $transaction) {
            $amountEuros = $transaction->total_amount_cents / 100;
            $percentage = $totalCents > 0 ? round(($transaction->total_amount_cents / $totalCents) * 100) : 0;

            $labels[] = $transaction->category;
            $data[] = $amountEuros;
            $colors[] = $this->generateColor($transaction->category);
            $tooltips[] = sprintf("%s: %.2f€ (%d%%)", $transaction->category, $amountEuros, $percentage);
        }

        return new DashboardData(
            chartLabels: $labels,
            chartData: $data,
            chartColors: $colors,
            tooltipLabels: $tooltips,
            totalAmount: $totalCents / 100,
            transactions: $transactions->map(fn($item) => [
                'category' => $item->category,
                'amount' => $item->total_amount_cents / 100,
                'percentage' => $totalCents > 0 ? round(($item->total_amount_cents / $totalCents) * 100) : 0
            ]),
            availableYears: $availableYears,
            availableMonths: $availableMonths,
            filterType: $filters['filterType'],
            selectedYear: $filters['year'],
            selectedMonth: $filters['month']
        );
    }

    private function generateColor(string $category): string
    {
        return '#' . dechex(crc32($category) & 0xFFFFFF);
    }


}
