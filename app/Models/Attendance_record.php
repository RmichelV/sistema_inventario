<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance_record extends Model
{
    /** @use HasFactory<\Database\Factories\AttendanceRecordFactory> */
    use HasFactory;

    protected $table = "attendance_records";
    protected $primaryKey = "id";
    protected $fillable = [
        "user_id",
        "attendance_status",
        "attendance_date",
        "check_in_at",
        "check_out_at",
        "minutes_worked"
    ];

    public function User(){
        return $this->belongsTo(User::class,"user_id","id");
    }

}
