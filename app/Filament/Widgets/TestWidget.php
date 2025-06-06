<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use function Livewire\before;

class TestWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected function getStats(): array
    {
        $start = $this->filters['startDate'];
        $end = $this->filters['endDate'];

        return [
            Stat::make('Novos usuários', 
            User::when($start, 
            fn ($query) => $query->whereDate('created_at', '>', $start))
            ->when($end, 
            fn ($query) => $query->whereDate('created_at', '<', $end))
                ->count())
                ->description('novos usuários')
                ->descriptionIcon('heroicon-m-users', IconPosition::Before)
                ->chart([10,3,27,19,20,40])
                ->color('success')
        ];
    }
}
