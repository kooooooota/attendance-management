<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class AttendanceRequestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_attendance_request_fails_if_start_time_is_later_than_end_time()
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

        $this->get(route('attendances.show', $attendance->id))
             ->assertStatus(200);

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

    public function test_attendance_request_fails_if_break_start_time_is_later_than_end_time()
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

        $this->get(route('attendances.show', $attendance->id))
             ->assertStatus(200);

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

    public function test_attendance_request_fails_if_break_end_time_is_later_than_end_time()
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

        $this->get(route('attendances.show', $attendance->id))
             ->assertStatus(200);

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

    public function test_attendance_request_fails_if_remarks_field_is_blank()
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

        $this->get(route('attendances.show', $attendance->id))
             ->assertStatus(200);

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

    public function test_users_can_submit_correction_requests_and_admin_can_view_the_request_in_approval_page_and_requests_list()
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

        $this->get(route('attendances.show', $attendance->id))
             ->assertStatus(200);

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
            'remarks' => '打刻漏れ',
        ];

        $this->post(route('attendances.request', $attendance->id), $requestData);

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $response = $this->actingAs($admin)
                         ->get(route('requests.index', ['tab' => 'pending']));
        $response->assertStatus(200)
                 ->assertSee('承認待ち')
                 ->assertSee('2026/04/01');

        $savedRequest = AttendanceRequest::where('attendance_id', $attendance->id)->first();

        $this->get(route('admins.requests.show', $savedRequest->id))
             ->assertOk()
             ->assertSee('2026年')
             ->assertSee('4月1日');
    }

    public function test_users_can_view_their_all_requests_in_the_pending_list()
    {
        $user = User::factory()->create();

        $attendanceData = [
            ['date' => '2026-04-01', 'in' => '09:00:00', 'out' => '18:00:00'],
            ['date' => '2026-04-02', 'in' => '10:00:00', 'out' => '19:00:00'],
        ];

        $attendances = [];
        foreach ($attendanceData as $data) {
            $attendances[] = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $data['date'],
                'punched_in_at' => $data['date'] . ' ' . $data['in'],
                'punched_out_at' => $data['date'] . ' ' . $data['out'],
            ]);
        }

        $this->actingAs($user);

        foreach ($attendances as $attendance) {
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
                'remarks' => '打刻漏れ',
            ];

            $this->post(route('attendances.request', $attendance->id), $requestData);
        }

        $response = $this->get(route('requests.index', ['tab' => 'pending']));
        $response->assertStatus(200)
                 ->assertSee('2026/04/01')
                 ->assertSee('2026/04/02');
    }

    public function test_users_can_view_their_all_requests_in_the_approved_list()
    {
        $user = User::factory()->create();

        $attendanceData = [
            ['date' => '2026-04-01', 'in' => '09:00:00', 'out' => '18:00:00'],
            ['date' => '2026-04-02', 'in' => '10:00:00', 'out' => '19:00:00'],
        ];

        $attendances = [];
        foreach ($attendanceData as $data) {
            $attendances[] = Attendance::create([
                'user_id' => $user->id,
                'work_date' => $data['date'],
                'punched_in_at' => $data['date'] . ' ' . $data['in'],
                'punched_out_at' => $data['date'] . ' ' . $data['out'],
            ]);
        }

        $this->actingAs($user);

        foreach ($attendances as $attendance) {
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
                'remarks' => '打刻漏れ',
            ];

            $this->post(route('attendances.request', $attendance->id), $requestData);

            AttendanceRequest::where('attendance_id', $attendance->id)
                            ->first()
                            ->update(['status' => 'approved']);
        }

        $response = $this->get(route('requests.index', ['tab' => 'approved']));
        $response->assertStatus(200)
                 ->assertSee('2026/04/01')
                 ->assertSee('2026/04/02');
    }

    public function test_users_can_view_request_details_when_click_details_button()
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

        $this->actingAs($user);
        
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
            'remarks' => '打刻漏れ',
        ];

        $this->post(route('attendances.request', $attendance->id), $requestData);

        $this->get(route('requests.index', ['tab' => 'pending']));
        
        $this->get(route('attendances.show', $attendance->id))
             ->assertOk();
    }
}
