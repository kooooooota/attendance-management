<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class AdminAttendanceShowTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_admin_can_view_attendance_details_when_click_details_link()
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

        $response = $this->get(route('admins.attendances.show', $attendance->id));
        $response->assertStatus(200)
                 ->assertSee('テスト 次郎');
    }

    public function test_admin_attendance_request_fails_if_start_time_is_later_than_end_time()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $punchIn = now()->parse('2026-04-01 09:00:00');

        $this->travelTo($punchIn);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $this->actingAs($admin)
             ->get(route('admins.attendances.show', $attendance->id))
             ->assertOk();

        $requestData = [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'punched_in_at' => '18:00',
            'punched_out_at' => '09:00',
            'remarks' => '打刻ミス'
        ];

        $response = $this->post(route('attendances.request', $attendance->id), $requestData);
        $response->assertSessionHasErrors([
            'punched_out_at' => '出勤時間もしくは退勤時間が不適切な値です'
        ]);
    }

    public function test_admin_attendance_request_fails_if_break_start_time_is_later_than_end_time()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $punchIn = now()->parse('2026-04-01 09:00:00');

        $this->travelTo($punchIn);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $this->actingAs($admin)
             ->get(route('admins.attendances.show', $attendance->id))
             ->assertOk();

        $requestData = [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'punched_in_at' => '09:00',
            'punched_out_at' => '18:00',
            'breaks' => [
                [
                    'punched_in_at' => '19:00',
                    'punched_out_at' => '13:00',
                ]
            ],
            'remarks' => '打刻漏れ',
        ];

        $response = $this->post(route('attendances.request', $attendance->id), $requestData);
        $response->assertSessionHasErrors([
            'breaks.0.punched_in_at' => '休憩時間が不適切な値です',
        ]);
    }

    public function test_admin_attendance_request_fails_if_break_end_time_is_later_than_end_time()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $punchIn = now()->parse('2026-04-01 09:00:00');

        $this->travelTo($punchIn);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $this->actingAs($admin)
             ->get(route('admins.attendances.show', $attendance->id))
             ->assertOk();

        $requestData = [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'punched_in_at' => '09:00',
            'punched_out_at' => '18:00',
            'breaks' => [
                [
                    'punched_in_at' => '12:00',
                    'punched_out_at' => '19:00',
                ]
            ],
            'remarks' => '打刻漏れ',
        ];

        $response = $this->post(route('attendances.request', $attendance->id), $requestData);
        $response->assertSessionHasErrors([
            'breaks.0.punched_out_at' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    public function test_admin_attendance_request_fails_if_remarks_field_is_blank()
    {
        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $user = User::factory()->create();

        $punchIn = now()->parse('2026-04-01 09:00:00');

        $this->travelTo($punchIn);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'punched_in_at' => $punchIn,
            'punched_out_at' => $punchIn->copy()->addHours(9),
        ]);

        $this->actingAs($admin)
             ->get(route('admins.attendances.show', $attendance->id))
             ->assertOk();

        $requestData = [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'punched_in_at' => '09:00',
            'punched_out_at' => '18:00',
            'breaks' => [
                [
                    'punched_in_at' => '12:00',
                    'punched_out_at' => '13:00',
                ]
            ],
            'remarks' => '',
        ];

        $response = $this->post(route('attendances.request', $attendance->id), $requestData);
        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }
}
