<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class salary extends Model
{
    /** @use HasFactory<\Database\Factories\SalaryFactory> */
    use HasFactory;

    protected $table = 'salaries';
    protected $primaryKey = 'id';
    protected $fillable = [
        'user_id',
        'base_salary',
        'salary_adjustment',
        'discounts',
        'total_salary',
        'paydate',
        'user_id_m'
    ];

    public function salary_adjustments()
    {
        return $this->hasMany(Salary_adjustment::class, 'user_id');
    }

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
