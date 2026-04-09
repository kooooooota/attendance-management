<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceRequest;

class AdminAttendanceRequestTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_admin_can_view_all_users_requests_in_the_pending_list()
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

             $this->actingAs($user)
             ->post(route('attendances.request', $attendance->id), $requestData);
        }

        $this->actingAs($admin);

        $response = $this->get(route('requests.index', ['tab' => 'pending']));
        $response->assertSee('テスト 太郎')
                 ->assertSee('テスト 次郎');
    }

    public function test_admin_can_view_all_users_requests_in_the_approved_list()
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

             $this->actingAs($user)
             ->post(route('attendances.request', $attendance->id), $requestData);

            AttendanceRequest::where('attendance_id', $attendance->id)
                            ->first()
                            ->update(['status' => 'approved']);
        }

        $this->actingAs($admin);

        $response = $this->get(route('requests.index', ['tab' => 'approved']));
        $response->assertSee('テスト 太郎')
                 ->assertSee('テスト 次郎');
    }

    public function test_admin_request_details_is_displayed_correctly()
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

        $this->actingAs($user)
             ->post(route('attendances.request', $attendance->id), $requestData);
        
        $this->actingAs($admin);

        $this->get(route('requests.index', ['tab' => 'pending']))
             ->assertOk();

        $savedRequest = AttendanceRequest::where('attendance_id', $attendance->id)->first();
        
        $response = $this->get(route('admins.requests.show', $savedRequest));
        $response->assertStatus(200)
                 ->assertSee('2026年')
                 ->assertSee('4月1日')
                 ->assertSee('12:00')
                 ->assertSee('13:00')
                 ->assertSee('打刻漏れ');
    }

    public function test_admin_can_approve_users_request()
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

        $this->actingAs($user)
             ->post(route('attendances.request', $attendance->id), $requestData);
             
        $savedRequest = AttendanceRequest::where('attendance_id', $attendance->id)->first();

        $this->actingAs($admin)
             ->get(route('admins.requests.show', $savedRequest))
             ->assertOk();


        $this->post(route('admins.requests.approval', $savedRequest), $requestData);

        $this->actingAs($user);
        
        $response = $this->get(route('attendances.show', $attendance->id));
        $response->assertStatus(200)
                 ->assertSee('2026年')
                 ->assertSee('4月1日')
                 ->assertSee('12:00')
                 ->assertSee('13:00');
    }
}
