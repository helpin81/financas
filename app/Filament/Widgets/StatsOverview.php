<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        
        $balance = \App\Models\Transaction::where('user_id', $user?->id)
            ->where('status', 'paid')
            ->sum('amount');
            
        $income = \App\Models\Transaction::where('user_id', $user?->id)
            ->where('type', 'income')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        $expense = \App\Models\Transaction::where('user_id', $user?->id)
            ->where('type', 'expense')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        return [
            Stat::make('Saldo Total', 'R$ ' . number_format($balance, 2, ',', '.'))
                ->description('Todas as contas')
                ->color($balance >= 0 ? 'success' : 'danger'),
            Stat::make('Receitas (Mês)', 'R$ ' . number_format($income, 2, ',', '.'))
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
            Stat::make('Despesas (Mês)', 'R$ ' . number_format(abs($expense), 2, ',', '.'))
                ->color('danger')
                ->chart([15, 4, 10, 2, 12, 4, 12]),
        ];
    }
}
