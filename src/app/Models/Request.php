<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    public function correctBreakTimes()
    {
        return $this->hasMany(CorrectBreakTime::class);
    }
}
