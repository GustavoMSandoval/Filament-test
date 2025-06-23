<?php

namespace App\Filament\Widgets;

use App\Models\ProductSale;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class StoreRevenueSummary extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        // Cálculo do faturamento do período
        $monthRevenue = ProductSale::query()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('SUM(value * quantity) as revenue')
            ->value('revenue') ?? 0;

        // Cálculo do faturamento do mês anterior
        $lastMonthRevenue = ProductSale::query()
            ->whereBetween('created_at', [
                $startDate->copy()->subMonth(),
                $endDate->copy()->subMonth()
            ])
            ->selectRaw('SUM(value * quantity) as revenue')
            ->value('revenue') ?? 0;

        // Cálculo do crescimento percentual
        $growth = $lastMonthRevenue ? 
            (($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100 : 
            0;

        // Cálculo da média diária
        $daysInPeriod = max(1, $startDate->diffInDays($endDate));
        $dailyAverage = $monthRevenue / $daysInPeriod;

        // Encontrar o proprietário que mais faturou
        $topOwner = User::query()
            ->select('users.name', DB::raw('SUM(product_sales.value * product_sales.quantity) as revenue'))
            ->join('products', 'users.id', '=', 'products.user_id')
            ->join('product_sales', 'products.id', '=', 'product_sales.product_id')
            ->whereBetween('product_sales.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name')
            ->orderBy('revenue', 'desc')
            ->first();

        // Estatísticas para retornar
        return [
            Stat::make('Faturamento do Período', number_format($monthRevenue, 2, ',', '.') . ' MT')
                ->description($growth >= 0 ? 
                    '↑ ' . number_format($growth, 1) . '% vs período anterior' : 
                    '↓ ' . number_format(abs($growth), 1) . '% vs período anterior')
                ->descriptionIcon($growth >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($growth >= 0 ? 'success' : 'danger')
                ->chart($this->getRevenueTrend($startDate, $endDate)),

            Stat::make('Proprietário Top', $topOwner ? $topOwner->name : 'Nenhum')
                ->description($topOwner ? 
                    number_format($topOwner->revenue, 2, ',', '.') . ' MT' : 
                    'Sem vendas registradas')
                ->color('primary')
                ->icon('heroicon-m-user-circle'),

            Stat::make('Média Diária', number_format($dailyAverage, 2, ',', '.') . ' MT')
                ->description('Baseado em ' . $daysInPeriod . ' dias')
                ->color('info')
                ->icon('heroicon-m-calendar-days'),
        ];
    }

    /**
     * Calcula a tendência de faturamento para o gráfico miniatura
     */
    protected function getRevenueTrend($startDate, $endDate): array
    {
        $revenueByDay = ProductSale::query()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(value * quantity) as daily_revenue'))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Preenche os dias faltantes com valor zero
        $days = collect();
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateStr = $currentDate->format('Y-m-d');
            $revenue = $revenueByDay->firstWhere('date', $dateStr)->daily_revenue ?? 0;
            $days->push($revenue);
            $currentDate->addDay();
        }

        return $days->toArray();
    }
}