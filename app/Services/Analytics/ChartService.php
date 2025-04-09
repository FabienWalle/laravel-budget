<?php

namespace App\Services\Analytics;

use IcehouseVentures\LaravelChartjs\Builder;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;

class ChartService
{
    public function buildTimeSeriesChart(array $labels, array $values, string $categoryName): Builder
    {
        return Chartjs::build()
            ->name('CategoryTimeSeriesChart')
            ->type('line')
            ->size(['width' => 800, 'height' => 400])
            ->labels($labels)
            ->datasets([
                [
                    'label' => "Dépenses ($categoryName)",
                    'data' => $values,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 2,
                    'tension' => 0.1,
                    'fill' => true
                ]
            ])
            ->options([
                'responsive' => true,
                'scales' => [
                    'y' => [
                        'beginAtZero' => true,
                        'title' => [
                            'display' => true,
                            'text' => 'Montant (€)'
                        ]
                    ],
                    'x' => [
                        'title' => [
                            'display' => true,
                            'text' => 'Période'
                        ]
                    ]
                ]
            ]);
    }
}
