<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{   
    protected $table = 'sales';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'sale_code',
        'customer_name',
        'sale_date',
        'pay_type',
        'final_price',
        'exchange_rate',
        'branch_id'
    ];

    public function saleItems(){
        return $this->hasMany(Sale_item::class,'sale_id', 'id');
    }

    /** @use HasFactory<\Database\Factories\SaleFactory> */
    use HasFactory;
}
