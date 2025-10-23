<?php

namespace Database\Seeders;

use App\Models\branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        branch::insert([[

            "name"=>"sucursal A",
            "address"=>"dir B"
        ],[

            "name"=>"sucursal B",
            "address"=>"dir B"
        ],[

            "name"=>"sucursal B",
            "address"=>"dir B"
        ],
        ]);
    }
}
