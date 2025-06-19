<?php

namespace App\Observers;

use App\Models\ProductSale;
use App\Models\Sale;

class ProductSaleObserver
{
    /**
     * Handle the ProductSale "created" event.
     */
    public function created(ProductSale $productSale): void
    {
        //
    }

    /**
     * Handle the ProductSale "updated" event.
     */
    public function updated(ProductSale $productSale): void
    {
        //
    }

    /**
     * Handle the ProductSale "deleted" event.
     */
    public function deleted(ProductSale $productSale)
    {
        $sale = Sale::find($productSale->sale_id);
        
        if ($sale && $sale->products()->count() === 0) {
            $sale->delete();
        }
    }

    /**
     * Handle the ProductSale "restored" event.
     */
    public function restored(ProductSale $productSale): void
    {
        //
    }

    /**
     * Handle the ProductSale "force deleted" event.
     */
    public function forceDeleted(ProductSale $productSale): void
    {
        //
    }
}
