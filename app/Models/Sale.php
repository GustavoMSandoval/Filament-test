<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sale extends Model
{
    protected $fillable = ['name', 'total', 'user_id'];
    
    // Desativa todos os eventos para melhor performance
    protected static $booted = [];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_sales')
            ->withPivot(['quantity', 'value'])
            ->withTimestamps();
    }

    public function calculateTotal()
    {
        // Consulta SQL otimizada que calcula tudo em uma única operação
        $total = DB::table('product_sales')
            ->where('sale_id', $this->id)
            ->selectRaw('COALESCE(SUM(quantity * value), 0) as total')
            ->value('total');

        // Atualização direta sem eventos
        DB::table('sales')
            ->where('id', $this->id)
            ->update(['total' => $total]);
        
        $this->total = $total;
    }

    public function removeProduct($productId)
    {
        $this->products()->detach($productId);
        
        if ($this->products()->count() === 0) {
            $this->delete();
            return null; // Indica que a venda foi removida
        }
        
        $this->calculateTotal();
        return $this; // Retorna a venda atualizada
    }
}