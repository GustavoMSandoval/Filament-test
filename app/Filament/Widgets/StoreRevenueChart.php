<?php

namespace App\Filament\Widgets;

use App\Models\ProductSale;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class StoreRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Faturamento da Loja';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        $monthData = Trend::query(ProductSale::query())
            ->between(start: $startDate, end: $endDate)
            ->perDay()
            ->sum('value * quantity');

        $yearData = Trend::query(ProductSale::query())
            ->between(start: now()->startOfYear(), end: now()->endOfYear())
            ->perMonth()
            ->sum('value * quantity');

        return [
            'datasets' => [
                [
                    'label' => 'Faturamento Diário',
                    'data' => $monthData->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(59, 130, 246, 0.5)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'tension' => 0.3,
                    'type' => 'bar',
                ],
                [
                    'label' => 'Faturamento Mensal',
                    'data' => $yearData->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 3,
                    'tension' => 0.3,
                    'type' => 'line',
                    'yAxisID' => 'right',
                ]
            ],
            'labels' => $monthData->map(fn (TrendValue $value) => Carbon::parse($value->date)->format('d M')),
        ];
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Faturamento Diário (MT)',
                    ],
                ],
                'right' => [
                    'position' => 'right',
                    'title' => [
                        'display' => true,
                        'text' => 'Faturamento Mensal (MT)',
                    ],
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
                'tooltip' => [
                    'callbacks' => [
                        'label' => function ($context) {
                            return $context->dataset->label . ': ' . number_format($context->raw, 2, ',', '.') . ' MT';
                        },
                    ],
                ],
            ],
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
