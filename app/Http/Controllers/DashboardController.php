<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use IcehouseVentures\LaravelChartjs\Facades\Chartjs;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $transactions = Transaction::query()
            ->where('amount', '<', 0)
            ->where('user_id', auth()->id())
            ->selectRaw('category, sum(abs(amount)) as total_amount_cents')
            ->groupBy('category')
            ->orderByDesc('total_amount_cents')
            ->get();

        $totalCents = $transactions->sum('total_amount_cents');

        $labels = [];
        $data = [];
        $backgroundColors = [];
        $tooltipLabels = [];

        foreach ($transactions as $transaction) {
            $amountEuros = $transaction->total_amount_cents / 100;
            $percentage = $totalCents > 0 ? round(($transaction->total_amount_cents / $totalCents) * 100) : 0;

            $labels[] = $transaction->category;
            $data[] = $amountEuros;
            $backgroundColors[] = '#' . substr(md5($transaction->category), 0, 6);
            $tooltipLabels[] = sprintf(
                "%s: %.2f€ (%d%%)",
                $transaction->category,
                $amountEuros,
                $percentage
            );
        }

        $chart = Chartjs::build()
            ->name('ExpensesChart')
            ->type('pie')
            ->size(['width' => 400, 'height' => 400])
            ->labels($labels)
            ->datasets([
                [
                    'label' => 'Dépenses par catégorie',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => '#fff',
                    'borderWidth' => 1,
                ]
            ])
            ->options([
                'responsive' => true,
                'maintainAspectRatio' => false,
                'plugins' => [
                    'legend' => [
                        'position' => 'right',
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => 'function(context) {
                                return ' . json_encode($tooltipLabels) . '[context.dataIndex];
                            }'
                        ]
                    ]
                ]
            ]);

        return view('dashboard', [
            'chart' => $chart,
            'total' => $totalCents / 100,
            'transactions' => $transactions->map(function($item) use ($totalCents) {
                return [
                    'category' => $item->category,
                    'amount' => $item->total_amount_cents / 100,
                    'percentage' => $totalCents > 0 ? round(($item->total_amount_cents / $totalCents) * 100) : 0
                ];
            })
        ]);
    }
}
