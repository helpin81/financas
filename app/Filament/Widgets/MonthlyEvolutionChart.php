<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class MonthlyEvolutionChart extends ChartWidget
{
    protected static ?string $heading = 'Evolução Mensal (Últimos 6 meses)';

    protected function getData(): array
    {
        $data = \App\Models\Transaction::query()
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, sum(case when type = 'income' then amount else 0 end) as income, sum(case when type = 'expense' then abs(amount) else 0 end) as expense")
            ->where('user_id', auth()->id())
            ->where('date', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Receitas',
                    'data' => $data->pluck('income')->toArray(),
                    'borderColor' => '#4ade80',
                ],
                [
                    'label' => 'Despesas',
                    'data' => $data->pluck('expense')->toArray(),
                    'borderColor' => '#f87171',
                ],
            ],
            'labels' => $data->pluck('month')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
