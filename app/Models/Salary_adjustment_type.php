<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary_adjustment_type extends Model
{
    /** @use HasFactory<\Database\Factories\SalaryAdjustmentTypeFactory> */
    use HasFactory;

    protected $table = "salary_adjustment_types";
    protected $primaryKey = "id";
    protected $fillable = ["name"];
}
