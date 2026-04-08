<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class BreakTimeTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

     use RefreshDatabase;

    public function test_the_break_in_button_works_correctly()
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
             ->assertSee('attendance-container__btn-break')
             ->assertSee('休憩入');
        
        $this->post(route('attendances.punch', ['type' => 'break_in']))
             ->assertStatus(302);

        $response = $this->get(route('attendances.time_stamp'));
        $response->assertStatus(200)
                 ->assertSee('休憩中');
    }

    public function test_users_can_take_multiple_breaks()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => now(),
            'punched_out_at' => null,
        ]);

        $this->actingAs($user)
             ->post(route('attendances.punch', ['type' => 'break_in']))
             ->assertStatus(302);
        $this->post(route('attendances.punch', ['type' => 'break_out']))
             ->assertStatus(302);

        $response = $this->get(route('attendances.time_stamp'));
        $response->assertStatus(200)
                 ->assertSee('attendance-container__btn-break')
                 ->assertSee('休憩入');
    }
    
    public function test_the_break_out_button_works_correctly()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => now(),
            'punched_out_at' => null,
        ]);

        $this->actingAs($user)
             ->post(route('attendances.punch', ['type' => 'break_in']))
             ->assertStatus(302);

        $response = $this->get(route('attendances.time_stamp'));
        $response->assertSee('attendance-container__btn-break')
                 ->assertSee('休憩戻');

        $this->post(route('attendances.punch', ['type' => 'break_out']))
             ->assertStatus(302);

        $response = $this->get(route('attendances.time_stamp'));
        $response->assertStatus(200)
                 ->assertSee('出勤中');
    }

    public function test_users_can_click_the_break_out_button_multiple_times()
    {
        $user = User::factory()->create();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => now(),
            'punched_out_at' => null,
        ]);

        $this->actingAs($user)
             ->post(route('attendances.punch', ['type' => 'break_in']))
             ->assertStatus(302);
        $this->post(route('attendances.punch', ['type' => 'break_out']))
             ->assertStatus(302);
        $this->post(route('attendances.punch', ['type' => 'break_in']))
             ->assertStatus(302);

        $response = $this->get(route('attendances.time_stamp'));
        $response->assertStatus(200)
                 ->assertSee('attendance-container__btn-break')
                 ->assertSee('休憩戻');
    }

    public function test_users_can_view_their_break_times_in_the_break_time_list()
    {
        $user = User::factory()->create();

        $this->travelTo(now()->parse('2026-04-01 09:00:00'));
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => now(),
            'punched_out_at' => null,
        ]);

        $this->actingAs($user);

        $this->travelTo(now()->parse('2026-04-01 12:00:00'));
        $this->post(route('attendances.punch', ['type' => 'break_in']))
             ->assertStatus(302);

        $this->travelTo(now()->parse('2026-04-01 13:00:00'));
        $this->post(route('attendances.punch', ['type' => 'break_out']))
             ->assertStatus(302);             

        $response = $this->get(route('attendances.list'));
        $response->assertStatus(200)
                 ->assertSee('04/01(水)')
                 ->assertSee('1:00');
    }
}
