<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation_item extends Model
{
    protected $table = 'reservation_items';
    protected $primaryKey = 'id';

    protected $fillable = [
        'reservation_id',
        'product_id',
        'quantity_products',
        'total_price',
        'exchange_rate',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservation::class, 'reservation_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    /** @use HasFactory<\Database\Factories\ReservationItemFactory> */
    use HasFactory;
}
