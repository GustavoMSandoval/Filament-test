<?php

namespace App\Filament\Resources\UserResource\RelationManagers;

use App\Models\ProductSale;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SalesRelationManager extends RelationManager
{
    protected static string $relationship = 'sales';
    protected static ?string $title = 'Itens Vendidos';
    

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ProductSale::query()
                    ->whereHas('product', function (Builder $query) {
                        $query->where('user_id', $this->getOwnerRecord()->id);
                    })
                    ->with(['product', 'sale'])
            )
            ->columns([
                Tables\Columns\TextColumn::make('sale.id')->label('ID da Venda'),
                Tables\Columns\TextColumn::make('product.name')->label('Produto'),
                Tables\Columns\TextColumn::make('quantity')->label('Quantidade'),
                Tables\Columns\TextColumn::make('value')->label('Valor Unitário')->money('BRL'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Subtotal')
                    ->getStateUsing(fn ($record) => $record->quantity * $record->value)
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i'),
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

}
