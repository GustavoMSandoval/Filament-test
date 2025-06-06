<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class TestChartWidget extends ChartWidget
{

    use InteractsWithPageFilters;

    protected static ?string $heading = 'Chart';

    protected function getData(): array
    {       
        
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];
        
        $data = Trend::model(User::class)
            ->between(
                start: $start ? Carbon::parse($start) : now()->subMonths(12),
                end: $end ? Carbon::parse($end) : now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Usuários',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ]
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date)
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
