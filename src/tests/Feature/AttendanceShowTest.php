<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceShowTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_the_name_field_on_attendance_details_screen_displays_the_logged_in_users_name()
    {
        $user = User::factory()->create([
            'name' => 'テスト 太郎',
        ]);

        $punchIn = now()->parse('2026-04-01 09:00:00');

        $this->travelTo($punchIn);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $response = $this->get(route('attendances.show', $attendance->id));
        $response->assertStatus(200)
                 ->assertSee('テスト 太郎');
    }

    public function test_the_date_field_on_attendance_details_screen_displays_selected_date()
    {
        $user = User::factory()->create();

        $punchIn = now()->parse('2026-04-01 09:00:00');

        $this->travelTo($punchIn);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $response = $this->get(route('attendances.show', $attendance->id));
        $response->assertStatus(200)
                 ->assertSee('2026年')
                 ->assertSee('4月1日');
    }

    public function test_the_attendance_field_displays_the_logged_in_users_attendance_records()
    {
        $user = User::factory()->create();

        $punchIn = now()->parse('2026-04-01 09:00:00');

        $this->travelTo($punchIn);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $response = $this->get(route('attendances.show', $attendance->id));
        $response->assertStatus(200)
                 ->assertSee('09:00')
                 ->assertSee('18:00');
    }
    public function test_break_times_field_display_the_logged_in_users_break_times_records()
    {
        $user = User::factory()->create();

        $punchIn = now()->parse('2026-04-01 09:00:00');

        $this->travelTo($punchIn);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $attendance->breakTimes()->create([
            'punched_in_at' => $punchIn->copy()->addHours(3),
            'punched_out_at' => $punchIn->copy()->addHours(4),
        ]);

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $response = $this->get(route('attendances.show', $attendance->id));
        $response->assertStatus(200)
                 ->assertSee('12:00')
                 ->assertSee('13:00');
    }
}
