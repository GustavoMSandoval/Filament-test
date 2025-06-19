<?php

namespace App\Filament\Pages;

use App\Models\Product as ProductModel;
use App\Models\Sale;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class Caixa extends Page implements HasForms
{
    use InteractsWithForms;

    public bool $showPrintModal = false;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static string $view = 'filament.pages.caixa';

    public $products = [];
    public $total = 0.00;
    public $sale;

    public function mount(): void
    {
        $this->form->fill();
        $this->showPrintModal = false;
        $this->sale = Sale::with('products')->latest()->first() ?? new Sale();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\Repeater::make('products')
                ->label('Produtos')
                ->schema([
                    Forms\Components\Select::make('product_id')
                        ->label('Produto')
                        ->options(ProductModel::all()->pluck('name', 'id'))
                        ->searchable()
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(function ($state, callable $set) {
                            $product = ProductModel::find($state);
                            if ($product) {
                                $set('value', $product->value);
                            }
                        }),

                    Forms\Components\TextInput::make('value')
                        ->label('Valor unitário')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Quantidade')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required()
                        ->dehydrated(),
                ])
                ->columns(3)
                ->required()
                ->afterStateUpdated(function ($state, callable $set) {
                    $total = collect($state)->sum(fn ($item) =>
                        (float) ($item['value'] ?? 0) * (int) ($item['quantity'] ?? 1)
                    );
                    $set('total', $total);
                }),

            Forms\Components\TextInput::make('total')
                ->label('Total da Venda')
                ->numeric()
                ->disabled(),
        ];
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $now = Carbon::now();

        $sale = Sale::create([
            'total' => collect($data['products'])->sum(fn ($item) =>
                (float) ($item['value'] ?? 0) * (int) ($item['quantity'] ?? 1)
            ),
            'sale_date' => $now->toDateString(),
            'sale_time' => $now->toTimeString(),
        ]);

        foreach ($data['products'] as $item) {
            $product = ProductModel::find($item['product_id']);
            $sale->products()->attach($product->id, [
                'quantity' => $item['quantity'],
                'value' => $item['value'],
                'user_id' => $product->user_id, // cada item leva o user correto
            ]);
        }

        $this->sale = Sale::with('products')->find($sale->id);
        $this->showPrintModal = true;
        $this->form->fill();

        Notification::make()
            ->title('Venda registrada com sucesso!')
            ->success()
            ->send();
    }

    protected function getFormModel(): Sale
    {
        return new Sale();
    }
}
