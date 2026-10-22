<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Modules\Member\Models\Member;
use Tests\TestCase;

final class VerifyMemberEmailTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/members/verify';

    /** Creates a member with a known plain token, the same way registration does */
    private function makeMember(?string $expiresAt = '+60 minutes'): array
    {
        $plainToken = Str::random(64);

        $member = Member::create([
            'full_name' => 'Asha Sharma',
            'email' => 'asha@example.com',
            'password' => 'Secret123',
            'email_verification_token' => hash('sha256', $plainToken),
            'email_verification_expires_at' => $expiresAt ? now()->modify($expiresAt) : null,
        ]);

        return [$member, $plainToken];
    }

    public function test_valid_token_verifies_the_email(): void
    {
        [$member, $plainToken] = $this->makeMember();

        $this->getJson(self::ENDPOINT . '?token=' . $plainToken)
            ->assertStatus(200)
            ->assertJsonPath('code', 200)
            ->assertJsonPath('type', 'EMAIL_VERIFIED');

        $this->assertNotNull($member->fresh()->email_verified_at);
    }

    public function test_wrong_token_returns_400_and_does_not_verify(): void
    {
        [$member] = $this->makeMember();

        $this->getJson(self::ENDPOINT . '?token=' . Str::random(64))
            ->assertStatus(400)
            ->assertJsonPath('type', 'INVALID_VERIFICATION_TOKEN');

        $this->assertNull($member->fresh()->email_verified_at);
    }

    public function test_expired_token_returns_400(): void
    {
        [$member, $plainToken] = $this->makeMember('-1 minute');

        $this->getJson(self::ENDPOINT . '?token=' . $plainToken)
            ->assertStatus(400)
            ->assertJsonPath('type', 'VERIFICATION_TOKEN_EXPIRED');

        $this->assertNull($member->fresh()->email_verified_at);
    }

    public function test_missing_token_returns_400(): void
    {
        $this->getJson(self::ENDPOINT)
            ->assertStatus(400)
            ->assertJsonPath('type', 'MISSING_VERIFICATION_TOKEN');
    }

    public function test_clicking_a_valid_link_twice_still_succeeds(): void
    {
        [, $plainToken] = $this->makeMember();

        $this->getJson(self::ENDPOINT . '?token=' . $plainToken)->assertStatus(200);
        $this->getJson(self::ENDPOINT . '?token=' . $plainToken)->assertStatus(200);
    }
}
