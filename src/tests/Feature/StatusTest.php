<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class StatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_attendance_status_is_displayed_correctly_when_off_the_clock()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get('/attendance')
             ->assertOk()
             ->assertSee('勤務外');
    }

    public function test_attendance_status_is_displayed_correctly_when_at_work()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => now(),
            'punched_out_at' => null,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('attendances.time_stamp'));

        $response->assertStatus(200)
                 ->assertSee('出勤中');
    }

    public function test_attendance_status_is_displayed_correctly_when_taking_a_break()
    {
        $user = User::factory()->create();

        $punchIn = now()->startOfDay()->addHours(9);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => null,
        ]);

        $attendance->breakTimes()->create([
            'punched_in_at' => $punchIn->copy()->addHours(3),
            'punched_out_at' => null,
        ]);

        $response = $this->actingAs($user)
                         ->get(route('attendances.time_stamp'));

        $response->assertStatus(200)
                 ->assertSee('休憩中');
    }

    public function test_attendance_status_is_displayed_correctly_when_finished_work()
    {
        $user = User::factory()->create();

        $punchIn = now()->startOfDay()->addHours(9);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $response = $this->actingAs($user)
                         ->get(route('attendances.time_stamp'));

        $response->assertStatus(200)
                 ->assertSee('退勤済');
    }
}
