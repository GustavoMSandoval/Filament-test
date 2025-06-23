<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;

class OwnerRevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Faturamento do Proprietário';
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $ownerId = $this->filters['owner_id'] ?? null;
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        if (!$ownerId) {
            return $this->getEmptyData();
        }

        $owner = User::find($ownerId);
        $monthData = Trend::query($owner->productSales())
            ->between(start: $startDate, end: $endDate)
            ->perDay()
            ->sum('value * quantity');

        $yearData = Trend::query($owner->productSales())
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
                ]
            ],
            'labels' => $monthData->map(function (TrendValue $value) {
                // Correção: Parse explícito para objeto Carbon
                return Carbon::parse($value->date)->format('d M');
            }),
        ];
    }

    protected function getOptions(): array
    {
        $ownerId = $this->filters['owner_id'] ?? null;
        $owner = $ownerId ? User::find($ownerId) : null;
        
        return [
            'plugins' => [
                'title' => [
                    'display' => true,
                    'text' => $owner ? 'Faturamento de ' . $owner->name : 'Faturamento do Proprietário',
                ],
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

    protected function getEmptyData(): array
    {
        return [
            'datasets' => [
                [
                    'label' => 'Selecione um proprietário',
                    'data' => [0],
                ]
            ],
            'labels' => ['Nenhum dado disponível'],
        ];
    }
}