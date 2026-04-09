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

        foreach ($users as $user) {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'work_date' => now()->today()->toDateString(),
                'punched_in_at' => '09:00',
                'punched_out_at' => '18:00',
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

        $currentDate = now()->toDateString();

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
            'work_date' => $prevDate,
            'punched_in_at' => '09:00',
            'punched_out_at' => '18:00',
        ]);

        $this->actingAs($admin)
             ->get(route('admins.attendances.index'))
             ->assertOk();

        $response = $this->get(route('admins.attendances.index', ['date' => $prevDate]));

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
            'work_date' => $nextDate,
            'punched_in_at' => '09:00',
            'punched_out_at' => '18:00',
        ]);

        $this->actingAs($admin)
             ->get(route('admins.attendances.index'))
             ->assertOk();

        $response = $this->get(route('admins.attendances.index', ['date' => $nextDate]));

        $response->assertStatus(200)
                 ->assertSee('09:00')
                 ->assertSee('18:00');
    }
}
