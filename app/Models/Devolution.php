<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Devolution extends Model
{
    /** @use HasFactory<\Database\Factories\DevolutionFactory> */
    use HasFactory;

    protected $table = 'devolutions';
    protected $primaryKey = 'id';
    protected $fillable = [
        'product_id',
        'quantity',
        'reason',
        'refund_amount'
    ];

    public function product(){
        return $this->belongsTo(Product::class,'product_id','id');
    }
}
