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

        $attendanceData = [
            ['date' => '2026-04-01', 'in' => '09:00:00', 'out' => '18:00:00'],
            ['date' => '2026-04-02', 'in' => '10:00:00', 'out' => '19:00:00'],
        ];

        $expectedStrings = [];
        foreach ($attendanceData as $data) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $data['date'],
                'punched_in_at' => $data['date'] . ' ' . $data['in'],
                'punched_out_at' => $data['date'] . ' ' . $data['out'],
            ]);

            $formattedDate = Carbon::parse($data['date'])->isoFormat('MM/DD(ddd)');

            $expectedStrings[] = $formattedDate;
            $expectedStrings[] = substr($data['in'], 0, 5);
            $expectedStrings[] = substr($data['out'], 0, 5);
        }

        $this->actingAs($user);

        $response = $this->get(route('attendances.list'));

        $response->assertStatus(200)
                 ->assertSeeInOrder($expectedStrings);
    }

    public function test_display_the_current_month()
    {
        $user = User::factory()->create();

        $this->travelTo(now()->parse('2026-04-01 09:00:00'));

        $this->actingAs($user);

        $response = $this->get(route('attendances.list'));

        $response->assertStatus(200)
                 ->assertSee('2026/04');
    }

    public function test_display_the_records_when_click_previous_month_link()
    {
        $user = User::factory()->create();

        $this->travelTo(now()->parse('2026-04-01 09:00:00'));

        $attendanceData = [
            ['date' => '2026-03-01', 'in' => '09:00:00', 'out' => '18:00:00'],
            ['date' => '2026-03-02', 'in' => '10:00:00', 'out' => '19:00:00'],
        ];

        $expectedStrings = [];
        foreach ($attendanceData as $data) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $data['date'],
                'punched_in_at' => $data['date'] . ' ' . $data['in'],
                'punched_out_at' => $data['date'] . ' ' . $data['out'],
            ]);

            $formattedDate = Carbon::parse($data['date'])->isoFormat('MM/DD(ddd)');

            $expectedStrings[] = $formattedDate;
            $expectedStrings[] = substr($data['in'], 0, 5);
            $expectedStrings[] = substr($data['out'], 0, 5);
        }

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $prevMonth = ['month' => '2026-03'];

        $response = $this->get(route('attendances.list', $prevMonth));

        $response->assertStatus(200)
                 ->assertSeeInOrder($expectedStrings);
    }

    public function test_display_the_records_when_click_next_month_link()
    {
        $user = User::factory()->create();

        $this->travelTo(now()->parse('2026-04-01 09:00:00'));

        $attendanceData = [
            ['date' => '2026-05-01', 'in' => '09:00:00', 'out' => '18:00:00'],
            ['date' => '2026-05-02', 'in' => '10:00:00', 'out' => '19:00:00'],
        ];

        $expectedStrings = [];
        foreach ($attendanceData as $data) {
            Attendance::create([
                'user_id' => $user->id,
                'work_date' => $data['date'],
                'punched_in_at' => $data['date'] . ' ' . $data['in'],
                'punched_out_at' => $data['date'] . ' ' . $data['out'],
            ]);

            $formattedDate = Carbon::parse($data['date'])->isoFormat('MM/DD(ddd)');

            $expectedStrings[] = $formattedDate;
            $expectedStrings[] = substr($data['in'], 0, 5);
            $expectedStrings[] = substr($data['out'], 0, 5);
        }

        $this->actingAs($user)
             ->get(route('attendances.list'))
             ->assertOk();

        $nextMonth = ['month' => '2026-05'];

        $response = $this->get(route('attendances.list', $nextMonth));

        $response->assertStatus(200)
                 ->assertSeeInOrder($expectedStrings);
    }

    public function test_view_attendance_details_when_click_details_button()
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
}
