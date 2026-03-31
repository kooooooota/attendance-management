<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-2 month', 'now');

        if (rand(1, 10) === 1) {
            $startTime = (clone $date)->setTime(9, 0, 0)->modify('+' . rand(1, 60) . ' minutes');
        } else {
            $startTime = (clone $date)->setTime(8, 0, 0)->modify('+' . rand(0, 60) . ' minutes');
        }

        $endTime = (clone $startTime)->modify('+9 hours')->modify(rand(0, 60) . ' minutes');

        return [
            'user_id' => User::factory(),
            'work_date' => $date->format('Y-m-d'),
            'punched_in_at' => $startTime,
            'punched_out_at' => $endTime,
        ];
    }
}
