<?php

namespace App\Filament\Widgets;

use App\Models\ProductSale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StoreRevenueSummary extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        $monthRevenue = ProductSale::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUM(value * quantity) as revenue')
            ->value('revenue') ?? 0;

        $lastMonthRevenue = ProductSale::query()
            ->whereBetween('created_at', [$startDate->copy()->subMonth(), $endDate->copy()->subMonth()])
            ->selectRaw('SUM(value * quantity) as revenue')
            ->value('revenue') ?? 0;

        $yearStart = now()->startOfYear();
        $yearEnd = now()->endOfYear();

        $yearRevenue = ProductSale::query()
            ->whereBetween('created_at', [$yearStart, $yearEnd])
            ->selectRaw('SUM(value * quantity) as revenue')
            ->value('revenue') ?? 0;

        $growth = $lastMonthRevenue ? (($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 0;
        $dailyAverage = now()->diffInDays($startDate) > 0 ? $monthRevenue / now()->diffInDays($startDate) : $monthRevenue;

        return [
            Stat::make('Faturamento do Período', number_format($monthRevenue, 2, ',', '.') . ' MT')
                ->description($growth >= 0 ? '↑ ' . number_format($growth, 1) . '% vs período anterior' : '↓ ' . number_format(abs($growth), 1) . '% vs período anterior')
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger'),

            Stat::make('Faturamento Anual', number_format($yearRevenue, 2, ',', '.') . ' MT')
                ->description(now()->format('Y'))
                ->color('primary'),

            Stat::make('Média Diária', number_format($dailyAverage, 2, ',', '.') . ' MT')
                ->description('Média de faturamento por dia')
                ->color('info'),
        ];
    }
}
