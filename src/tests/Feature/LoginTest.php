<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    use RefreshDatabase;

    public function test_user_login_requires_email()
    {
        $this->get('/login')->assertStatus(200);

        $formData = [
            'email' => '',
            'password' => 'password',
        ];

        $response = $this->post('/login', $formData);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_user_login_requires_password()
    {
        $this->get('/login')->assertStatus(200);

        $formData = [
            'email' => 'test@example.com',
            'password' => '',
        ];

        $response = $this->post('/login', $formData);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_user_login_fails_with_invalid_email()
    {
        $user = User::factory()->create([
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);

        $this->assertGuest();
    }

    public function test_admin_login_requires_email()
    {
        $this->get('/admin/login')->assertStatus(200);

        $formData = [
            'email' => '',
            'password' => 'password',
        ];

        $response = $this->post('/admin/login', $formData);
        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }

    public function test_admin_login_requires_password()
    {
        $this->get('/admin/login')->assertStatus(200);

        $formData = [
            'email' => 'test@example.com',
            'password' => '',
        ];

        $response = $this->post('/admin/login', $formData);
        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    public function test_admin_login_fails_with_invalid_email()
    {
        $user = User::factory()->create([
            'name' => 'テスト 太郎',
            'email' => 'test@example.com',
            'is_admin' => true,
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);
        
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません'
        ]);

        $this->assertGuest();
    }

    


}
