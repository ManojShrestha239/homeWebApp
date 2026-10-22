<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Modules\Member\Models\Member;
use Tests\TestCase;

final class RememberMeTest extends TestCase
{
    use RefreshDatabase;

    private const LOGIN = '/api/v1/members/login';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();   // avoid rate-limit counts leaking between tests
    }

    private function verifiedMember(): void
    {
        $member = Member::create([
            'full_name' => 'Asha Sharma',
            'email' => 'asha@example.com',
            'password' => 'Secret123',
        ]);
        $member->forceFill(['email_verified_at' => now()])->save();
    }

    public function test_without_remember_the_token_lasts_the_normal_time(): void
    {
        $this->verifiedMember();

        $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'Secret123',
            'remember' => false,
        ])->assertStatus(200)
            ->assertJsonPath('data.expires_in', (int) config('member_auth.ttl_minutes') * 60);
    }

    public function test_with_remember_the_token_lasts_the_longer_time(): void
    {
        $this->verifiedMember();

        $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'Secret123',
            'remember' => true,
        ])->assertStatus(200)
            ->assertJsonPath('data.expires_in', (int) config('member_auth.remember_ttl_minutes') * 60);
    }

    public function test_remember_is_optional(): void
    {
        $this->verifiedMember();

        $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'Secret123',
        ])->assertStatus(200)
            ->assertJsonPath('data.expires_in', (int) config('member_auth.ttl_minutes') * 60);
    }

    public function test_default_ttl_is_restored_after_issuing_a_remember_token(): void
    {
        $this->verifiedMember();

        $this->postJson(self::LOGIN, [
            'email' => 'asha@example.com',
            'password' => 'Secret123',
            'remember' => true,
        ])->assertStatus(200);

        // The package default must not stay changed for other code
        $this->assertSame(
            (int) config('jwt.ttl'),
            (int) \PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth::factory()->getTTL(),
        );
    }
}
