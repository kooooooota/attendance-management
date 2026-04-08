<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class PunchOutTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_the_punch_out_button_works_correctly()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => now(),
            'punched_out_at' => null,
        ]);

        $this->actingAs($user)
             ->get(route('attendances.time_stamp'))
             ->assertOk()
             ->assertSee('attendance-container__btn-attendance')
             ->assertSee('退勤');

        $this->post(route('attendances.punch', ['type' => 'out']))
             ->assertStatus(302);

        $response = $this->get(route('attendances.time_stamp'));
        $response->assertStatus(200)
                 ->assertSee('退勤済');
    }

    public function test_users_can_view_their_work_finish_times_in_the_attendance_list()
    {
        $user = User::factory()->create();

        $this->travelTo(now()->parse('2026-04-01 09:00:00'));

        $this->actingAs($user);

        $this->post(route('attendances.punch', ['type' => 'in']))
             ->assertStatus(302);

        $this->travelTo(now()->parse('2026-04-01 18:00:00'));
        $this->post(route('attendances.punch', ['type' => 'out']))
             ->assertStatus(302);

        $response = $this->get(route('attendances.list'));
             
        $response->assertStatus(200)
                 ->assertSee('04/01(水)')
                 ->assertSee('18:00');
    }
}
