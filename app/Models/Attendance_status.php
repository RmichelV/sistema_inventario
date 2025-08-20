<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance_status extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceStatusFactory> */
    use HasFactory;
    protected $table = "attendance_statuses";
    protected $primaryKey = "id";
    protected $fillable = ["name","paid"];
}
