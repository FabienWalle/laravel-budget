<?php

namespace App\Services\Dashboard;

use IcehouseVentures\LaravelChartjs\Builder;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;

class ChartService
{
    public function buildExpensesChart(array $labels, array $data, array $colors, array $tooltips): Builder
    {
        return Chartjs::build()
            ->name('ExpensesChart')
            ->type('pie')
            ->size(['width' => 400, 'height' => 400])
            ->labels($labels)
            ->datasets([[
                'label' => 'Dépenses par catégorie',
                'data' => $data,
                'backgroundColor' => $colors,
                'borderColor' => '#fff',
                'borderWidth' => 1,
            ]])
            ->options([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => ['position' => 'right'],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'function(context) {
                                return ' . json_encode($tooltips) . '[context.dataIndex];
                            }'
                        ]
                    ]
                ]
            ]);
    }
}
