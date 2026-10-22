<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Modules\Member\Models\Member;
use Tests\TestCase;

final class RegisterMemberTest extends TestCase
{
    use RefreshDatabase;   // gives each test a fresh, empty database

    private const ENDPOINT = '/api/v1/members/register';

    public function test_valid_registration_returns_201_and_stores_unverified_member(): void
    {
        Mail::fake();   // pretend to send email so nothing really goes out

        $response = $this->postJson(self::ENDPOINT, [
            'full_name' => 'Asha Sharma',
            'email' => 'Asha@Example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('code', 201)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.email', 'asha@example.com')   // email was lowercased
            ->assertJsonMissingPath('data.password');            // password never leaks

        $this->assertDatabaseHas('members', [
            'email' => 'asha@example.com',
            'email_verified_at' => null,
        ]);
    }

    public function test_duplicate_email_returns_409(): void
    {
        $this->withoutExceptionHandling();   // TEMPORARY: shows the real error. Remove after debugging.
        Mail::fake();
        Member::create([
            'full_name' => 'Asha Sharma',
            'email' => 'asha@example.com',
            'password' => 'Secret123',
        ]);

        $response = $this->postJson(self::ENDPOINT, [
            'full_name' => 'Asha Again',
            'email' => 'asha@example.com',
            'password' => 'Secret123',
            'password_confirmation' => 'Secret123',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('code', 409)
            ->assertJsonPath('status', false);
    }

    public function test_weak_password_returns_422_with_field_errors(): void
    {
        $response = $this->postJson(self::ENDPOINT, [
            'full_name' => 'Asha Sharma',
            'email' => 'asha@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('code', 422)
            ->assertJsonStructure(['data' => ['errors' => ['password']]]);
    }
}
