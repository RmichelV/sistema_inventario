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
        "salary_adjustment_type",
        "amount",
        "description",
        "date"
    ];
    
    public function User(){
        return $this->belongsTo(User::class,"user_id","id");
    }
}
