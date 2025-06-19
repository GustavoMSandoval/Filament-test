<?php

namespace App\Providers;

use App\Models\ProductSale;
use App\Observers\ProductSaleObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ProductSale::observe(ProductSaleObserver::class);
    }
}
