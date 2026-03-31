<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::all()->each(function ($user) {
            for ($i = 0; $i < 60; $i++) {
                $date = Carbon::now()->subDays($i);

                if ($date->isWeekend()) {
                    continue;
                }

                if (rand(1, 30) === 1) {
                    $startTime = (clone $date)->setTime(9, 0, 0)->modify('+' . rand(1, 60) . ' minutes');
                } else {
                    $startTime = (clone $date)->setTime(8, 0, 0)->modify('+' . rand(0, 60) . ' minutes');
                }

                $endTime = (clone $startTime)->modify('+9 hours')->modify(rand(0, 60) . ' minutes');

                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'work_date' => $date->format('Y-m-d'),
                    'punched_in_at' => $startTime,
                    'punched_out_at' => $endTime,
                ]);

                
                $breakStart = (clone $date)->setTime(12, rand(0, 15), 0);
                $afternoonBreakStart = (clone $date)->setTime(15, rand(0, 15), 0);
                
                $break = [
                    ['start' => $breakStart, 'duration' => 60],
                ];
                if (rand(1, 10) !== 1) {
                    $break[] = ['start' => $afternoonBreakStart, 'duration' => 15];
                }
    
                foreach ($break as $b) {
                    $start = $b['start'];
                    $end = (clone $start)->modify("+{$b['duration']} minutes");

                    BreakTime::create([
                        'attendance_id' => $attendance->id,
                        'punched_in_at' => $start,
                        'punched_out_at' => $end,
                    ]);
                }
            }
        });
    }
}
