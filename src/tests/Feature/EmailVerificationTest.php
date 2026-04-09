<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use App\Models\User;


class EmailVerificationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_registration_sends_verification_email()
    {
        $this->get('/register')->assertStatus(200);

        Notification::fake();

        $formData = [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ];

        $this->post('/register', $formData);

        $user = User::where('email', 'taro@example.com')->first();

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    public function test_user_can_verify_email()
    {
        $this->get('/register')->assertStatus(200);

        Notification::fake();

        $email = 'taro@example.com';

        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => $email,
            'password' => 'password',
            'password_confirmation' => 'password', 
        ]);

        $response->assertRedirect('/attendance');

        $user = User::where('email', $email)->first();
        $this->assertNotNull($user);

        $this->get('/attendance')->assertRedirect('/email/verify');
        $this->get('/email/verify')->assertStatus(200)->assertSee('認証はこちらから');

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function test_user_can_verify_email_and_access_attendance()
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'taro@example.com')->first();

        $verifyUrl = '';
        Notification::assertSentTo($user, VerifyEmail::class, function ($notification) use ($user, &$verifyUrl) {
            $verifyUrl = $notification->toMail($user)->actionUrl;
            return true;
        });

        $response = $this->get($verifyUrl);

        $response->assertRedirect('/attendance?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }
}