<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Modules\Member\Models\Member;
use Tests\TestCase;

final class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const FORGOT = '/api/v1/members/password/forgot';
    private const RESET = '/api/v1/members/password/reset';

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();   // rate-limit counts must not leak between tests
        Mail::fake();     // never send real email during tests
    }

    /** Creates a member and gives them a known reset token, like the forgot endpoint does */
    private function memberWithResetToken(string $expiresAt = '+30 minutes'): array
    {
        $member = Member::create([
            'full_name' => 'Asha Sharma',
            'email' => 'asha@example.com',
            'password' => 'OldPass123',
        ]);

        $plainToken = Str::random(64);
        $member->forceFill([
            'password_reset_token' => hash('sha256', $plainToken),
            'password_reset_expires_at' => now()->modify($expiresAt),
        ])->save();

        return [$member, $plainToken];
    }

    public function test_forgot_password_returns_200_for_a_registered_email(): void
    {
        Member::create([
            'full_name' => 'Asha Sharma',
            'email' => 'asha@example.com',
            'password' => 'OldPass123',
        ]);

        $this->postJson(self::FORGOT, ['email' => 'asha@example.com'])
            ->assertStatus(200)
            ->assertJsonPath('type', 'PASSWORD_RESET_REQUESTED');

        $this->assertNotNull(Member::where('email', 'asha@example.com')->first()->password_reset_token);
    }

    public function test_forgot_password_gives_the_same_reply_for_an_unknown_email(): void
    {
        $this->postJson(self::FORGOT, ['email' => 'nobody@example.com'])
            ->assertStatus(200)
            ->assertJsonPath('type', 'PASSWORD_RESET_REQUESTED');

        Mail::assertNothingSent();
    }

    public function test_valid_token_changes_the_password_and_clears_the_token(): void
    {
        [$member, $plainToken] = $this->memberWithResetToken();

        $this->postJson(self::RESET, [
            'token' => $plainToken,
            'password' => 'NewPass456',
            'password_confirmation' => 'NewPass456',
        ])->assertStatus(200)
            ->assertJsonPath('type', 'PASSWORD_RESET_SUCCESS');

        $fresh = $member->fresh();
        $this->assertTrue(Hash::check('NewPass456', $fresh->password));
        $this->assertNull($fresh->password_reset_token);
        $this->assertNull($fresh->password_reset_expires_at);
    }

    public function test_token_can_be_used_only_once(): void
    {
        [, $plainToken] = $this->memberWithResetToken();

        $payload = [
            'token' => $plainToken,
            'password' => 'NewPass456',
            'password_confirmation' => 'NewPass456',
        ];

        $this->postJson(self::RESET, $payload)->assertStatus(200);
        $this->postJson(self::RESET, $payload)
            ->assertStatus(400)
            ->assertJsonPath('type', 'INVALID_RESET_TOKEN');
    }

    public function test_expired_token_returns_400(): void
    {
        [, $plainToken] = $this->memberWithResetToken('-1 minute');

        $this->postJson(self::RESET, [
            'token' => $plainToken,
            'password' => 'NewPass456',
            'password_confirmation' => 'NewPass456',
        ])->assertStatus(400)
            ->assertJsonPath('type', 'RESET_TOKEN_EXPIRED');
    }

    public function test_wrong_token_returns_400(): void
    {
        $this->memberWithResetToken();

        $this->postJson(self::RESET, [
            'token' => Str::random(64),
            'password' => 'NewPass456',
            'password_confirmation' => 'NewPass456',
        ])->assertStatus(400)
            ->assertJsonPath('type', 'INVALID_RESET_TOKEN');
    }

    public function test_weak_new_password_returns_422(): void
    {
        [, $plainToken] = $this->memberWithResetToken();

        $this->postJson(self::RESET, [
            'token' => $plainToken,
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertStatus(422)
            ->assertJsonStructure(['data' => ['errors' => ['password']]]);
    }
}
