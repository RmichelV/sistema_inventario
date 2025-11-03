<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reservation extends Model
{
    protected $table = 'reservations';
    protected $primaryKey = 'id';

    protected $fillable = [
        'customer_id',
        'total_amount',
        'advance_amount',
        'rest_amount',
        'exchange_rate',
        'pay_type',
        'branch_id',
    ];

    protected $casts = [
        'total_amount' => 'float',
        'advance_amount' => 'float',
        'rest_amount' => 'float',
        'exchange_rate' => 'float',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function reservationItems()
    {
        return $this->hasMany(Reservation_item::class, 'reservation_id', 'id');
    }

    /** @use HasFactory<\Database\Factories\ReservationFactory> */
    use HasFactory;
}
