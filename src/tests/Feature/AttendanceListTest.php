<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AttendanceListTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_users_can_view_all_their_attendance_records()
    {
        $user = User::factory()->create();

        $firstWorkDay = now()->yesterday();
        $secondWorkDay = now()->today();

        $attendanceData = [
            ['date' => $firstWorkDay->toDateString(), 'in' => $firstWorkDay->copy()->setTime(9, 0, 0), 'out' => $firstWorkDay->copy()->setTime(18, 0, 0)],
            ['date' => $secondWorkDay->toDateString(), 'in' => $secondWorkDay->copy()->setTime(10, 0, 0), 'out' => $secondWorkDay->copy()->setTime(19, 0, 0)],
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

        $this->actingAs($user);

        $response = $this->get(route('attendances.list'));

        $response->assertStatus(200)
                 ->assertSeeInOrder($expectedStrings);
    }

    public function test_display_the_current_month()
    {
        $user = User::factory()->create();

        $currentMonth = now()->format('Y/m');

        $this->actingAs($user);

        $response = $this->get(route('attendances.list'));

        $response->assertStatus(200)
                 ->assertSee($currentMonth);
    }

    public function test_display_the_records_when_click_previous_month_link()
    {
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

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $response = $this->get(route('attendances.list', ['month' => $prevMonth]));

        $response->assertStatus(200)
                 ->assertSeeInOrder($expectedStrings);
    }

    public function test_display_the_records_when_click_next_month_link()
    {
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

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $response = $this->get(route('attendances.list', ['month' => $nextMonth]));

        $response->assertStatus(200)
                 ->assertSeeInOrder($expectedStrings);
    }

    public function test_users_can_view_attendance_details_when_click_details_link()
    {
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

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $response = $this->get(route('attendances.show', $attendance->id));
        $response->assertStatus(200)
                 ->assertSee($currentYear)
                 ->assertSee($currentDate);
    }
}
