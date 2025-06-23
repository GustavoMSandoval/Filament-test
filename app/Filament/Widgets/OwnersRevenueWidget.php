<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\TableWidget;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class OwnersRevenueWidget extends TableWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Faturamento por Proprietário';
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $startDate = $this->filters['startDate'] ?? now()->startOfMonth();
        $endDate = $this->filters['endDate'] ?? now()->endOfMonth();

        return User::query()
            ->select(
                'users.id',
                'users.name as owner_name',
                DB::raw('SUM(product_sales.value * product_sales.quantity) as total_revenue')
            )
            ->join('products', 'users.id', '=', 'products.user_id')
            ->join('product_sales', 'products.id', '=', 'product_sales.product_id')
            ->whereBetween('product_sales.created_at', [$startDate, $endDate])
            ->groupBy('users.id', 'users.name')
            ->orderBy('total_revenue', 'desc');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('owner_name')
                    ->label('Proprietário')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Faturamento')
                    ->money('MZN')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('percentage')
                    ->label('Participação')
                    ->suffix('%')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        $totalRevenue = DB::table('product_sales')
                            ->whereBetween('created_at', [
                                $this->filters['startDate'] ?? now()->startOfMonth(),
                                $this->filters['endDate'] ?? now()->endOfMonth()
                            ])
                            ->select(DB::raw('SUM(value * quantity) as total'))
                            ->value('total') ?? 1;
                        
                        return number_format(($record->total_revenue / $totalRevenue) * 100, 2);
                    }),
            ])
            ->emptyStateHeading('Nenhuma venda registrada para proprietários neste período');
    }
}