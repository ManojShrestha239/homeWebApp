<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Member\Models\Member;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

final class LoginMemberTest extends TestCase
{
    use RefreshDatabase;

    private const LOGIN = '/api/v1/members/login';
    private const ME = '/api/v1/members/me';

    protected function setUp(): void
    {
        parent::setUp();

        // Forget every rate-limit count before each test, so one test can't use up another's attempts
        Cache::flush();
    }

    /** Creates a member. $verified decides whether the email is confirmed. */
    private function makeMember(bool $verified = true): Member
    {
        $member = Member::create([
            'full_name' => 'Asha Sharma',
            'email' => 'asha@example.com',
            'password' => 'Secret123',   // hashed automatically by the model cast
        ]);

        // email_verified_at is not in $fillable (on purpose), so we set it directly here
        if ($verified) {
            $member->forceFill(['email_verified_at' => now()])->save();
        }

        return $member;
    }

    public function test_verified_member_gets_a_token(): void
    {
        $this->makeMember();

        $response = $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'Secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('code', 200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('type', 'LOGIN_SUCCESS')
            ->assertJsonPath('data.token_type', 'bearer')
            ->assertJsonPath('data.member.email', 'asha@example.com')
            ->assertJsonMissingPath('data.member.password');

        $this->assertNotEmpty($response->json('data.access_token'));
    }

    public function test_wrong_password_returns_401(): void
    {
        $this->makeMember();

        $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'WrongPass1',
        ])->assertStatus(401)
            ->assertJsonPath('type', 'INVALID_CREDENTIALS');
    }

    public function test_unknown_email_returns_the_same_401(): void
    {
        $this->postJson(self::LOGIN, [
            'email' => 'nobody@example.com',
            'password' => 'Secret123',
        ])->assertStatus(401)
            ->assertJsonPath('type', 'INVALID_CREDENTIALS');
    }

    public function test_unverified_member_returns_403_even_with_correct_password(): void
    {
        $this->makeMember(verified: false);

        $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'Secret123',
        ])->assertStatus(403)
            ->assertJsonPath('type', 'EMAIL_NOT_VERIFIED');
    }

    public function test_missing_fields_return_422(): void
    {
        $this->postJson(self::LOGIN, [])
            ->assertStatus(422)
            ->assertJsonPath('code', 422)
            ->assertJsonStructure(['data' => ['errors' => ['email', 'password']]]);
    }

    public function test_me_returns_the_member_when_token_is_sent(): void
    {
        $this->makeMember();

        $login = $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'Secret123',
        ]);

        // If login failed, stop here and show why. This avoids a misleading 401 from /me
        $login->assertStatus(200);
        $token = $login->json('data.access_token');
        $this->assertIsString($token);

        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson(self::ME)
            ->assertStatus(200)
            ->assertJsonPath('data.email', 'asha@example.com');
    }

    public function test_me_without_token_returns_401(): void
    {
        $this->getJson(self::ME)
            ->assertStatus(401)
            ->assertJsonPath('type', 'UNAUTHENTICATED');
    }

    public function test_debug_token_parsing(): void
    {
        $this->makeMember();

        $response = $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'Secret123',
        ]);

        dump('LOGIN BODY', $response->json());

        $token = $response->json('data.access_token');

        dump('TOKEN', $token, 'LENGTH', strlen((string) $token), 'SEGMENTS', substr_count((string) $token, '.') + 1);

        $this->assertTrue(true);
    }
}
