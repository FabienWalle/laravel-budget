<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\ChartService;
use App\Services\Dashboard\DashboardDataService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardDataService $dashboardDataService,
        private readonly ChartService $chartService
    ) {}

    public function index(Request $request): View
    {
        $filterType = $this->sanitizeFilterType($request->input('filter', 'all'));
        $selectedYear = $request->input('year');
        $selectedMonth = $request->input('month');

        if ($filterType === 'month' && ($monthYear = $request->input('monthYear'))) {
            [$selectedYear, $selectedMonth] = array_pad(explode('-', $monthYear), 2, null);
        }

        switch ($filterType) {
            case 'all':
                $selectedYear = $selectedMonth = null;
                break;
            case 'year':
                $selectedMonth = null;
                break;
        }

        $dashboardData = $this->dashboardDataService->getDashboardData(
            auth()->id(),
            [
                'filterType' => $filterType,
                'year' => $selectedYear,
                'month' => $selectedMonth
            ]
        );

        $chart = $this->chartService->buildExpensesChart(
            $dashboardData->chartLabels,
            $dashboardData->chartData,
            $dashboardData->chartColors,
            $dashboardData->tooltipLabels
        );

        return view('dashboard.index', [
            'chart' => $chart,
            'total' => $dashboardData->totalAmount,
            'transactions' => $dashboardData->transactions,
            'availableYears' => $dashboardData->availableYears,
            'availableMonths' => $dashboardData->availableMonths,
            'currentFilter' => $dashboardData->filterType,
            'selectedYear' => $dashboardData->selectedYear,
            'selectedMonth' => $dashboardData->selectedMonth,
        ]);
    }

    private function sanitizeFilterType(?string $filter): string
    {
        $validFilters = ['all', 'year', 'month'];
        return in_array($filter, $validFilters) ? $filter : 'all';
    }
}
