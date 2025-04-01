<?php

namespace App\DataTransferObjects;

use Illuminate\Support\Collection;

class DashboardData
{
    public function __construct(
        public array $chartLabels,
        public array $chartData,
        public array $chartColors,
        public array $tooltipLabels,
        public float $totalAmount,
        public Collection $transactions,
        public Collection $availableYears,
        public array $availableMonths,
        public string $filterType,
        public ?int $selectedYear,
        public ?int $selectedMonth
    ) {}
}
