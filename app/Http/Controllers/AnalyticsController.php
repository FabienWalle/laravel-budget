<?php

namespace App\Http\Controllers;

use App\Services\Analytics\AnalyticsDataService;
use App\Services\Analytics\ChartService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly AnalyticsDataService $analyticsService,
        private readonly ChartService $chartService
    ) {}

    public function index(Request $request): View
    {
        $customCategory = $request->input('category');
        $userId = auth()->id();

        $data = $this->analyticsService->getCategoryTimeSeries($userId, $customCategory);
        $availableCategories = $this->analyticsService->getAvailableCustomCategories($userId);

        $chart = $this->chartService->buildTimeSeriesChart(
            $data['labels'],
            $data['values'],
            $customCategory ?? 'Toutes catégories'
        );

        return view('analytics.index', [
            'chart' => $chart,
            'availableCategories' => $availableCategories,
            'selectedCategory' => $customCategory
        ]);
    }
}
