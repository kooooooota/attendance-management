<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class GeneralUserRegistrationTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;
    
    public function test_registration_requires_name()
    {
        $this->get('/register')->assertStatus(200);

        $formData = [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $formData);
        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください'
        ]);
    }

    public function test_registration_requires_email()
    {
        $this->get('/register')->assertStatus(200);

        $formData = [
            'name' => 'テスト 太郎',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $formData);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください'
        ]);
    }

    public function test_registration_fails_if_password_is_too_short()
    {
        $this->get('/register')->assertStatus(200);

        $formData = [
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ];

        $response = $this->post('/register', $formData);
        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください'
        ]);
    }
    
    public function test_registration_fails_if_passwords_do_not_match()
    {
        $this->get('/register')->assertStatus(200);

        $formData = [
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'diffrent_password',
        ];

        $response = $this->post('/register', $formData);
        $response->assertSessionHasErrors([
            'password' => 'パスワードと一致しません'
        ]);
    }

    public function test_registration_requires_password()
    {
        $this->get('/register')->assertStatus(200);

        $formData = [
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $formData);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください'
        ]);
    }

    public function test_new_users_can_register()
    {
        $this->get('/register')->assertStatus(200);

        $formData = [
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];

        $response = $this->post('/register', $formData);

        $this->assertDatabaseHas('users', [
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertAuthenticatedAs($user);
    }
}
