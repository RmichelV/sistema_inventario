<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary_adjustment extends Model
{
    /** @use HasFactory<\Database\Factories\SalaryAdjustmentFactory> */
    use HasFactory;
    protected $table = "salary_adjustments";
    protected $primaryKey = "id";
    protected $fillable = [
        "user_id",
        "adjustment_type_id",
        "amount",
        "description",
        "date"
    ];
    
    public function User(){
        return $this->belongsTo(User::class,"user_id","id");
    }

    public function AdjustmentType(){   
        return $this->belongsTo(Salary_adjustment_type::class,"adjustment_type_id","id");
    }
}
