<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\ChartService;
use App\Services\Dashboard\DashboardDataService;
use Illuminate\Http\JsonResponse;
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
        $filters = [
            'filterType' => $request->input('filter', 'all'),
            'year' => $request->input('year'),
            'month' => $request->input('month'),
        ];

        $dashboardData = $this->dashboardDataService->getDashboardData(
            auth()->id(),
            $filters
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

    public function getMonths(Request $request): JsonResponse
    {
        $months = $this->dashboardDataService
            ->getTransactionService()
            ->getAvailableMonths(auth()->id(), $request->query('year'));

        return response()->json($months);
    }
}
