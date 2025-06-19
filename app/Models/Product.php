<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'value',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->belongsToMany(Sale::class, 'product_sales')
            ->withPivot(['quantity', 'value'])
            ->withTimestamps();
    }

     public function productSales(): HasMany
    {
        return $this->hasMany(ProductSale::class);
    }
}
