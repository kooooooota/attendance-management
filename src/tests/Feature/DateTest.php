<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class DateTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_get_current_date()
    {
        $user = User::factory()->create([
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $this->travelTo(now()->parse('2026-04-01 09:00:00'));

        $this->actingAs($user)
             ->get('/attendance')
             ->assertOk()
             ->assertSee('2026年4月1日(水)')
             ->assertSee('09:00');
    }
}
