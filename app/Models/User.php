<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    protected $table = "users";
    protected $primaryKey = "id";
    protected $fillable = [
        'name',
        'address',
        'phone',
        'branch_id',
        'role_id',
        'base_salary',
        'hire_date',
        'email',
        'password',
    ];

    public function Role(){
        return $this->belongsTo(Role::class,'role_id','id');
    }
    public function salary_adjustments()
    {
        // Esto asume que el modelo Salary_adjustment existe y tiene la FK 'user_id'.
        return $this->hasMany(Salary_adjustment::class, 'user_id', 'id');
    }
    public function attendance_records()
    {
        // Esto asume que el modelo Salary_adjustment existe y tiene la FK 'user_id'.
        return $this->hasMany(Attendance_record::class, 'user_id', 'id');
    }

    public function branch(){
        return $this->belongsTo(Branch::class, 'branch_id','id');
    }
    
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
