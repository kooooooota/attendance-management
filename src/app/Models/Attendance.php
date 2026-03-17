<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function getTotalBreakMinutes()
    {
        return $this->breakTimes->sum(function($bt) {
            if ($bt->punched_in_at && $bt->punched_out_at) {
                return Carbon::parse($bt->punched_in_at)->diffInMinutes($bt->punched_out_at);
            }
            return 0;
        });
    }

    public function getWorkMinutes()
    {
        if (!$this->punched_in_at || !$this->punched_out_at) {
            return 0;
        }

        $in = Carbon::parse($this->punched_in_at);
        $out = Carbon::parse($this->punched_out_at);

        $stayMinutes = $in->diffInMinutes($out);

        return $stayMinutes - $this->getTotalBreakMinutes();
    }

    private function formatMinutesToTime($minutes)
    {
        if ($minutes === 0) return '';
        $hours = floor($minutes / 60);
        $min = $minutes % 60;
        return sprintf('%d:%02d', $hours, $min);
    }

    public function getWorkTimeDisplay()
    {
        return $this->formatMinutesToTime($this->getWorkMinutes());
    }

    public function getBreakTimeDisplay()
    {
        return $this->formatMinutesToTime($this->getTotalBreakMinutes());
    }

    protected $casts = [
        'work_date' => 'date',
        'punched_in_at' => 'datetime',
        'punched_out_at' => 'datetime',
    ];
}
