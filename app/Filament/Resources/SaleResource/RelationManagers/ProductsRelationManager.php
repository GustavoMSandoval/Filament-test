<?php

namespace App\Filament\Resources\SaleResource\RelationManagers;

use App\Filament\Resources\SaleResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    protected static ?string $title = 'Produto';
    protected static bool $canCreate = false;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nome'),
                Tables\Columns\TextColumn::make('pivot.quantity')->label('Quantidade'),
                Tables\Columns\TextColumn::make('pivot.value')
                    ->label('Valor Unitário')
                    ->money('BRL'),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('BRL')
                    ->getStateUsing(fn ($record) => $record->pivot->quantity * $record->pivot->value),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Sem ação de criar
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form([
                        Forms\Components\TextInput::make('pivot.quantity')
                            ->label('Quantidade')
                            ->required()
                            ->numeric()
                            ->minValue(1),
                    ])
                    ->action(function ($record, array $data): void {
                        DB::table('product_sales')
                            ->where('sale_id', $this->ownerRecord->id)
                            ->where('product_id', $record->id)
                            ->update(['quantity' => $data['pivot']['quantity']]);
                        
                        $this->ownerRecord->calculateTotal();
                        
                        Notification::make()
                            ->title('Quantidade atualizada')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()
                    ->label('Remover da Venda')
                    ->modalHeading('Remover Produto da Venda')
                    ->modalDescription('Tem certeza que deseja remover este produto da venda?')
                    ->action(function ($record): void {
                        try {
                            // Inicia transação para segurança
                            DB::beginTransaction();
                            
                            // Remove o produto da venda (apenas do pivot)
                            DB::table('product_sales')
                                ->where('sale_id', $this->ownerRecord->id)
                                ->where('product_id', $record->id)
                                ->delete();
                            
                            // Verifica se ainda existem produtos na venda
                            $hasProducts = DB::table('product_sales')
                                ->where('sale_id', $this->ownerRecord->id)
                                ->exists();
                            
                            if (!$hasProducts) {
                                // Se não houver mais produtos, deleta a venda
                                DB::table('sales')
                                    ->where('id', $this->ownerRecord->id)
                                    ->delete();
                                
                                // Notificação e redirecionamento
                                Notification::make()
                                    ->title('Venda excluída')
                                    ->body('A venda foi excluída pois não tinha mais produtos')
                                    ->success()
                                    ->send();
                                
                                // Redireciona para a lista de vendas
                                $this->redirect(
                                    SaleResource::getUrl('index'),
                                    navigate: true // Para navegação SPA
                                );
                            } else {
                                // Se ainda houver produtos, recalcula o total
                                $this->ownerRecord->calculateTotal();
                                
                                Notification::make()
                                    ->title('Produto removido')
                                    ->success()
                                    ->send();
                            }
                            
                            DB::commit();
                        } catch (QueryException $e) {
                            DB::rollBack();
                            Notification::make()
                                ->title('Erro ao remover produto')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    })
                    ->successNotificationTitle(function () {
                        return $this->ownerRecord->exists 
                            ? 'Produto removido da venda' 
                            : 'Último produto removido - venda excluída';
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->action(function ($records): void {
                            $productIds = $records->pluck('id')->toArray();
                            
                            DB::table('product_sales')
                                ->where('sale_id', $this->ownerRecord->id)
                                ->whereIn('product_id', $productIds)
                                ->delete();
                            
                            // Verifica se a venda ficou vazia
                            $hasProducts = DB::table('product_sales')
                                ->where('sale_id', $this->ownerRecord->id)
                                ->exists();
                            
                            if (!$hasProducts) {
                                DB::table('sales')
                                    ->where('id', $this->ownerRecord->id)
                                    ->delete();
                                
                                $this->redirect(
                                    SaleResource::getUrl('index'),
                                    navigate: true
                                );
                            } else {
                                $this->ownerRecord->calculateTotal();
                            }
                        }),
                ]),
            ]);
    }
}