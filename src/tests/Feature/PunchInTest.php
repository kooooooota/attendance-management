<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class PunchInTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_the_punch_in_button_works_correctly()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get('/attendance')
             ->assertOk()
             ->assertSee('attendance-container__btn-attendance')
             ->assertSee('出勤');
        
        $this->post(route('attendances.punch', ['type' => 'in']))
             ->assertStatus(302);

        $response = $this->get(route('attendances.time_stamp'));

        $response->assertStatus(200)
                 ->assertSee('出勤中');
    }

    public function test_users_can_only_press_the_clock_in_button_once_a_day()
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
                 ->assertDontSee('<button class="attendance-container__btn-attendance" name="type" value="in" class="btn">出勤</button>');
    }

    public function test_users_can_view_their_work_start_times_in_the_attendance_list()
    {
          $user = User::factory()->create();

          $date = now()->today();

          $this->actingAs($user)
               ->get('/attendance')
               ->assertOk();

          $this->travelTo($date->copy()->setTime(9, 0, 0));
          $this->post(route('attendances.punch', ['type' => 'in']))
               ->assertStatus(302);

          $response = $this->get(route('attendances.list'));
               
          $response->assertStatus(200)
                    ->assertSee($date->isoFormat('MM/DD(ddd)'))
                    ->assertSee('09:00');
    }
}
