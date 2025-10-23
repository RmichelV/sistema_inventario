<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class product_branch extends Model
{
    /** @use HasFactory<\Database\Factories\ProductBranchFactory> */
    use HasFactory;
    protected $table = 'product_branches';
    protected $fillable = [
        'branch_id',
        'product_id',
        'quantity_in_stock',
        'units_per_box',
        'last_update',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
