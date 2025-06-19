<x-filament-panels::page>
    <x-filament::page>
        {{ $this->form }}

        <x-filament::button wire:click="submit" class="mt-4">
            Finalizar Venda
        </x-filament::button>

        <!-- Modal de Impressão -->
        <!-- Modal de Impressão -->
        @if ($showPrintModal)
            <div 
                x-data
                x-init="window.setTimeout(() => window.print(), 300)"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
                wire:ignore.self
            >
                <div class="bg-white p-6 rounded-lg shadow-lg w-1/2 print:w-full print:shadow-none print:p-0" id="printContent">
                    <h2 class="text-xl font-bold mb-4">Recibo de Venda</h2>

                    @foreach ($sale->products as $product)
                        <p>
                            <strong>Produto:</strong> {{ $product->name }}<br>
                            <strong>Quantidade:</strong> {{ $product->pivot->quantity }}<br>
                            <strong>Subtotal:</strong> R$ {{ number_format($product->pivot->value * $product->pivot->quantity, 2, ',', '.') }}
                        </p>
                        <hr class="my-2">
                    @endforeach

                    <p class="mt-4 font-bold text-right">
                        Total: R$ {{ number_format($sale->total, 2, ',', '.') }}
                    </p>

                    <div class="mt-6 flex justify-end no-print">
                        <button onclick="window.print()" class="px-4 py-2 ">Imprimir</button>
                        <button wire:click="$set('showPrintModal', false)" class="ml-2 text-gray-700">Cancelar</button>
                    </div>
                </div>
            </div>
        @endif



        <!-- Estilos -->
        <style>
            @media print {
                body * {
                    visibility: hidden;
                }
                #printContent, #printContent * {
                    visibility: visible;
                }
                #printContent {
                    position: absolute;
                    left: 0;
                    top: 0;
                    width: 100%;
                }
                .no-print {
                    display: none !important;
                }
            }
        </style>

    </x-filament::page>
</x-filament-panels::page>
