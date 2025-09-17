<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

//models 
use App\Models\Usd_exchange_rate;
class UsdExchangeRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Usd_exchange_rate::create([
            
                "exchange_rate"=> "6.96",
        ]);
    }
}
