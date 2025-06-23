<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OwnerRevenueChart;
use App\Filament\Widgets\OwnersRevenueWidget;
use App\Filament\Widgets\StoreRevenueChart;
use App\Filament\Widgets\StoreRevenueSummary;
use App\Models\User;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
                Select::make('owner_id')
                    ->label('Proprietário')
                    ->placeholder('Todos os proprietários')
                    ->options(User::query()->pluck('name', 'id'))
                    ->searchable()
                    ->reactive(),
            ])->columns(3),
        ]);
    }

    protected function getHeaderWidgets(): array
    {
        // Mostra diferentes widgets dependendo se um proprietário está selecionado
        if ($this->filters['owner_id'] ?? false) {
            return [
                StoreRevenueSummary::class,
                OwnerRevenueChart::class,
            ];
        }
        
        return [
            StoreRevenueSummary::class,
            StoreRevenueChart::class,
            OwnersRevenueWidget::class,
        ];
    }
}