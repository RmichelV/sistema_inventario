<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class branch extends Model
{
    /** @use HasFactory<\Database\Factories\BranchFactory> */
    use HasFactory;
    protected $table ="branches";
    protected $primaryKey = "id";
    protected $fillable = [
        "name",
        "address",
    ];

    
}
