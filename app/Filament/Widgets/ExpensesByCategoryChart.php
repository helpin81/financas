<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class ExpensesByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Despesas por Categoria (Mês Atual)';

    protected function getData(): array
    {
        $data = \App\Models\Transaction::query()
            ->join('categories', 'transactions.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, sum(transactions.amount) as total')
            ->where('transactions.user_id', auth()->id())
            ->where('transactions.type', 'expense')
            ->whereMonth('transactions.date', now()->month)
            ->whereYear('transactions.date', now()->year)
            ->groupBy('categories.name')
            ->pluck('total', 'category_name')
            ->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Despesas por Categoria',
                    'data' => array_values($data),
                    'backgroundColor' => [
                        '#f87171', '#fb923c', '#facc15', '#4ade80', '#2dd4bf', '#60a5fa', '#a78bfa', '#f472b6',
                    ],
                ],
            ],
            'labels' => array_keys($data),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
