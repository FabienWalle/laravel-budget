<?php

namespace App\Services\Analytics;

use App\Models\Transaction;
use Illuminate\Support\Collection;

class AnalyticsDataService
{
    public function getAvailableCustomCategories(int $userId): Collection
    {
        return Transaction::query()->where('user_id', $userId)
            ->whereNotNull('custom_category')
            ->select('custom_category')
            ->distinct()
            ->orderBy('custom_category')
            ->pluck('custom_category');
    }

    public function getCategoryTimeSeries(int $userId, ?string $category): array
    {
        $query = Transaction::query()->where('user_id', $userId)
            ->where('amount', '<', 0)
            ->selectRaw("
    TO_CHAR(operation_date, 'YYYY-MM') as month,
    SUM(ABS(amount)) as total_amount
")
            ->groupBy('month')
            ->orderBy('month');

        if ($category) {
            $query->where('custom_category', $category);
        }

        $results = $query->get();

        $labels = [];
        $values = [];

        foreach ($results as $result) {
            $labels[] = $result->month;
            $values[] = $result->total_amount / 100;
        }

        return [
            'labels' => $labels,
            'values' => $values
        ];
    }
}
