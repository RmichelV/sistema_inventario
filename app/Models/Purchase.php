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
    ];

    public function product(){
        return $this->belongsTo(Product::class,"product_id","id");
    }
}
