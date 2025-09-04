<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usd_exchange_rate extends Model
{
    /** @use HasFactory<\Database\Factories\UsdExchangeRateFactory> */
    use HasFactory;
    protected $table = "usd_exchange_rates";
    protected $primaryKey = "id";
    protected $fillable = [
        "exchange_rate"
    ];

}
