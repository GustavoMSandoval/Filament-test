<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductSale extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'user_id',
        'quantity',
        'value',
    ];

    protected static function booted()
    {
        static::saved(function ($productSale) {
            // Adia a atualização para evitar sobrecarga
            dispatch(function () use ($productSale) {
                $productSale->sale()->first()->calculateTotal();
            });
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}