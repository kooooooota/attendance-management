<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRequest extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimeRequests()
    {
        return $this->hasMany(BreakTimeRequest::class);
    }

    protected $casts = [
        'punched_in_at' => 'datetime',
        'punched_out_at' => 'datetime',
    ];
}
