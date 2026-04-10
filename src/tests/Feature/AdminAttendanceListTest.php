<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_admin_can_view_all_users_attendance_records()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);
        
        $users = User::factory()
            ->count(2)
            ->sequence(
                ['name' => 'テスト 太郎'],
                ['name' => 'テスト 次郎']
            )
            ->create();

        $date = now()->today();

        foreach ($users as $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $date->toDateString(),
                'punched_in_at' => $date->copy()->setTime(9, 0, 0),
                'punched_out_at' => $date->copy()->setTime(18, 0, 0),
            ]);
        }

        $this->actingAs($admin);

        $response = $this->get(route('admins.attendances.index'));

        $response->assertStatus(200)
                 ->assertSee('テスト 太郎')
                 ->assertSee('テスト 次郎');
    }

    public function test_display_current_date_in_attendance_list_for_admin()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $currentDate = now()->format('Y/m/d');

        $this->actingAs($admin);

        $response = $this->get(route('admins.attendances.index'));
        $response->assertStatus(200)
                 ->assertSee($currentDate);
    }

    public function test_display_the_records_when_click_previous_date_link()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $prevDate = now()->subDay()->startOfDay();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $prevDate->toDateString(),
            'punched_in_at' => $prevDate->copy()->setTime(9, 0, 0),
            'punched_out_at' => $prevDate->copy()->setTime(18, 0, 0),
        ]);

        $this->actingAs($admin)
             ->get(route('admins.attendances.index'))
             ->assertOk();

        $response = $this->get(route('admins.attendances.index', ['date' => $prevDate->toDateString()]));

        $response->assertStatus(200)
                 ->assertSee('09:00')
                 ->assertSee('18:00');
    }

    public function test_display_the_records_when_click_next_date_link()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $nextDate = now()->addDay()->startOfDay();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $nextDate->toDateString(),
            'punched_in_at' => $nextDate->copy()->setTime(9, 0, 0),
            'punched_out_at' => $nextDate->copy()->setTime(18, 0, 0),
        ]);

        $this->actingAs($admin)
             ->get(route('admins.attendances.index'))
             ->assertOk();

        $response = $this->get(route('admins.attendances.index', ['date' => $nextDate->toDateString()]));

        $response->assertStatus(200)
                 ->assertSee('09:00')
                 ->assertSee('18:00');
    }
}
