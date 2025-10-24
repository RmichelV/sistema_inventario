<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseFactory> */
    use HasFactory;

    protected $table = "purchases";
    protected $primaryKey = "id";
    protected $fillable = [
        "product_id",
        "purchase_quantity",
        "purchase_date",
        "branch_id",
        "unit_price",
        "total_price",
    ];

    protected $casts = [
        'purchase_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function product(){
        return $this->belongsTo(Product::class,"product_id","id");
    }
}
