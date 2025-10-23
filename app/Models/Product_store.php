<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_store extends Model
{
    /** @use HasFactory<\Database\Factories\ProductStoreFactory> */
    use HasFactory;

    protected $table = "product_stores";
    protected $primaryKey = "id";
    protected $fillable = [
        "product_id",
        "quantity",
        "unit_price",
        'price_multiplier',
        'last_update',
        'branch_id',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'price_multiplier' => 'decimal:4',
    ];

    public function product(){
        return $this->belongsTo(Product::class,"product_id","id");
    }
}
