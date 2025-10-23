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
        'img_product'
    ];
    public function productStore()
    {
        return $this->hasOne(Product_store::class);
    }

    public function productBranches()
    {
        return $this->hasMany(product_branch::class, 'product_id', 'id');
    }
}
