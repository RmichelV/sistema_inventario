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
        'unit_price',
        'units_per_box',
        'last_update',
    ];

    /**
     * Attribute casting for correct types.
     */
    protected $casts = [
        'quantity_in_stock' => 'integer',
        'units_per_box' => 'integer',
        'unit_price' => 'decimal:2',
        'last_update' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
