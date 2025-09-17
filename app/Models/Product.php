<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    protected $table = "products";
    protected $primaryKey = "id";
    protected $fillable = [
        'name',
        'code',
        'img_product',
        'quantity_in_stock',
        'units_per_box',
    ];
    public function productStore()
    {
        return $this->hasOne(Product_store::class);
    }
}
