<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\StoreRevenueChart;
use App\Filament\Widgets\StoreRevenueSummary;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function filtersForm(Form $form): Form
    {
        return $form->schema([
            Section::make('Filtros')->schema([
                DatePicker::make('startDate')
                    ->label('Data Inicial')
                    ->default(now()->startOfMonth()),
                DatePicker::make('endDate')
                    ->label('Data Final')
                    ->default(now()->endOfMonth()),
            ])->columns(2),
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StoreRevenueSummary::class,
            StoreRevenueChart::class,
        ];
    }
}
