<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminUsersListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_admin_can_view_names_and_emails_of_all_users()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $users = User::factory()
            ->count(2)
            ->sequence(
                [
                    'name' => 'テスト 太郎',
                    'email' => 'taro@example.com'
                ],
                [
                    'name' => 'テスト 次郎',
                    'email' => 'jiro@example.com'
                ]
            )
            ->create();

        $this->actingAs($admin);

        $response = $this->get(route('admins.users.index'));
        $response->assertStatus(200)
                 ->assertSee('テスト 太郎')
                 ->assertSee('taro@example.com')
                 ->assertSee('テスト 次郎')
                 ->assertSee('jiro@example.com');
    }

    public function test_attendance_records_is_displayed_correctly()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $date = now()->today();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date->toDateString(),
            'punched_in_at' => $date->copy()->setTime(9, 0, 0),
            'punched_out_at' => $date->copy()->setTime(18, 0, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admins.users.show', $user->id));
        $response->assertStatus(200)
                 ->assertSee('09:00')
                 ->assertSee('18:00');
    }

    public function test_admin_can_view_the_users_records_when_click_previous_month_link()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $prevMonthFirstDay = now()->subMonth()->startOfMonth();
        $prevMonth = $prevMonthFirstDay->format('Y-m');

        $attendanceData = [
            ['date' => $prevMonthFirstDay->toDateString(), 'in' => $prevMonthFirstDay->copy()->setTime(9, 0, 0), 'out' => $prevMonthFirstDay->copy()->setTime(18, 0, 0)],
            ['date' => $prevMonthFirstDay->copy()->addDay()->toDateString(), 'in' => $prevMonthFirstDay->copy()->addDay()->setTime(10, 0, 0), 'out' => $prevMonthFirstDay->copy()->addDay()->setTime(19, 0, 0)],
        ];

        $expectedStrings = [];
        foreach ($attendanceData as $data) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $data['date'],
                'punched_in_at' => $data['in'],
                'punched_out_at' => $data['out'],
            ]);

            $formattedDate = Carbon::parse($data['date'])->isoFormat('MM/DD(ddd)');

            $expectedStrings[] = $formattedDate;
            $expectedStrings[] = substr($data['in'], 11, 5);
            $expectedStrings[] = substr($data['out'], 11, 5);
        }

        $this->actingAs($admin)
             ->get(route('admins.users.show', $user->id))
             ->assertOk();

        $response = $this->get(route('admins.users.show', ['id' => $user->id, 'month' => $prevMonth]));

        $response->assertStatus(200)
                 ->assertSeeInOrder($expectedStrings);
    }

    public function test_admin_can_view_the_users_records_when_click_next_month_link()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $nextMonthFirstDay = now()->addMonth()->startOfMonth();
        $nextMonth = $nextMonthFirstDay->format('Y-m');

        $attendanceData = [
            ['date' => $nextMonthFirstDay->toDateString(), 'in' => $nextMonthFirstDay->copy()->setTime(9, 0, 0), 'out' => $nextMonthFirstDay->copy()->setTime(18, 0, 0)],
            ['date' => $nextMonthFirstDay->copy()->addDay()->toDateString(), 'in' => $nextMonthFirstDay->copy()->addDay()->setTime(10, 0, 0), 'out' => $nextMonthFirstDay->copy()->addDay()->setTime(19, 0, 0)],
        ];

        $expectedStrings = [];
        foreach ($attendanceData as $data) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $data['date'],
                'punched_in_at' => $data['in'],
                'punched_out_at' => $data['out'],
            ]);

            $formattedDate = Carbon::parse($data['date'])->isoFormat('MM/DD(ddd)');

            $expectedStrings[] = $formattedDate;
            $expectedStrings[] = substr($data['in'], 11, 5);
            $expectedStrings[] = substr($data['out'], 11, 5);
        }

        $this->actingAs($admin)
             ->get(route('admins.users.show', $user->id))
             ->assertOk();

        $response = $this->get(route('admins.users.show', ['id' => $user->id, 'month' => $nextMonth]));

        $response->assertStatus(200)
                 ->assertSeeInOrder($expectedStrings);
    }

    public function test_admin_can_view_attendance_details_when_click_details_link()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $date = now()->today();

        $currentYear = $date->format('Y年');
        $currentDate = $date->format('n月j日');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => $date->toDateString(),
            'punched_in_at' => $date->copy()->setTime(9, 0, 0),
            'punched_out_at' => $date->copy()->setTime(18, 0, 0),
        ]);

        $this->actingAs($admin)
             ->get(route('admins.users.show', $user->id))
             ->assertOk();

        $response = $this->get(route('admins.attendances.show', $attendance->id));
        $response->assertStatus(200)
                 ->assertSee($currentYear)
                 ->assertSee($currentDate);
    }
}
