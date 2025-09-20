<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale_item extends Model
{
    protected $table = 'sale_items';

    protected $primaryKey = 'id';

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity_products',
        'total_price',
        'exchange_rate'
    ];

    public function sale(){
        return $this->belongsTo(Sale::class, 'sale_id', 'id');
    }
    
    public function product(){
        return $this->belongsTo(Product::class, 'product_id','id');
    }

    /** @use HasFactory<\Database\Factories\SaleItemFactory> */
    use HasFactory;
}
