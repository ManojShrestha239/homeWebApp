<?php

declare(strict_types=1);

namespace Tests\Feature\Member;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleProfile;
use Mockery;
use Modules\Member\Models\Member;
use Modules\Member\Services\MemberOAuthService;
use Tests\TestCase;

final class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    private const REDIRECT = '/api/v1/members/oauth/google/redirect';
    private const CALLBACK = '/api/v1/members/oauth/google/callback';

    /** Builds a fake Google profile, the same shape Google returns */
    private function googleProfile(string $email = 'asha@gmail.com', bool $verified = true): GoogleProfile
    {
        $profile = new GoogleProfile();
        $profile->map([
            'id' => 'google-123',
            'name' => 'Asha Sharma',
            'email' => $email,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);
        $profile->setRaw(['verified_email' => $verified]);

        return $profile;
    }

    /** Replaces the Socialite facade so no real calls reach Google */
    private function fakeGoogle(?GoogleProfile $profile): void
    {
        $driver = Mockery::mock();
        $driver->shouldReceive('stateless')->andReturnSelf();
        $driver->shouldReceive('with')->andReturnSelf();
        $driver->shouldReceive('redirect')->andReturn(
            redirect('https://accounts.google.com/o/oauth2/auth?client_id=test'),
        );
        $driver->shouldReceive('user')->andReturn($profile);

        Socialite::shouldReceive('driver')->with('google')->andReturn($driver);
    }

    /** Stores a valid one-time state, as the redirect endpoint would */
    private function validState(string $state = 'valid-state'): string
    {
        Cache::put(MemberOAuthService::stateKey($state), true, now()->addMinutes(10));

        return $state;
    }

    public function test_redirect_endpoint_returns_google_url_as_json(): void
    {
        $this->fakeGoogle(null);

        $this->getJson(self::REDIRECT)
            ->assertStatus(200)
            ->assertJsonPath('code', 200)
            ->assertJsonPath('type', 'GOOGLE_AUTH_URL')
            ->assertJsonPath('data.url', 'https://accounts.google.com/o/oauth2/auth?client_id=test');
    }

    public function test_new_google_user_is_created_verified_and_redirected_with_token(): void
    {
        $this->fakeGoogle($this->googleProfile());
        $state = $this->validState();

        $response = $this->get(self::CALLBACK . '?state=' . $state . '&code=abc');

        $response->assertRedirectContains('/oauth/callback#');
        $response->assertRedirectContains('token=');

        $member = Member::where('google_id', 'google-123')->first();
        $this->assertNotNull($member);
        $this->assertNull($member->password);
        $this->assertNotNull($member->email_verified_at);
    }

    public function test_existing_verified_email_account_is_linked_to_google(): void
    {
        // A member who registered with a password earlier and already verified their email
        $existing = Member::create([
            'full_name' => 'Asha Sharma',
            'email' => 'asha@gmail.com',
            'password' => 'Secret123',
        ]);
        $existing->forceFill(['email_verified_at' => now()])->save();

        $this->fakeGoogle($this->googleProfile());
        $state = $this->validState();

        $this->get(self::CALLBACK . '?state=' . $state . '&code=abc')
            ->assertRedirectContains('token=');

        $this->assertSame('google-123', $existing->fresh()->google_id);
        $this->assertSame(1, Member::count());   // linked, not duplicated
    }

    public function test_unverified_google_email_is_rejected(): void
    {
        $this->fakeGoogle($this->googleProfile(verified: false));
        $state = $this->validState();

        $this->get(self::CALLBACK . '?state=' . $state . '&code=abc')
            ->assertRedirectContains('error=GOOGLE_EMAIL_NOT_VERIFIED');

        $this->assertSame(0, Member::count());
    }

    public function test_missing_or_reused_state_is_rejected(): void
    {
        $this->fakeGoogle($this->googleProfile());
        $state = $this->validState();

        // First use works
        $this->get(self::CALLBACK . '?state=' . $state . '&code=abc')
            ->assertRedirectContains('token=');

        // Second use of the same state is refused (it was deleted)
        $this->get(self::CALLBACK . '?state=' . $state . '&code=abc')
            ->assertRedirectContains('error=OAUTH_STATE_INVALID');
    }

    public function test_user_who_cancels_on_google_gets_an_error_redirect(): void
    {
        $this->get(self::CALLBACK . '?error=access_denied')
            ->assertRedirectContains('error=GOOGLE_SIGNIN_CANCELLED');
    }

    public function test_google_only_member_cannot_log_in_with_a_password(): void
    {
        $this->fakeGoogle($this->googleProfile());
        $this->get(self::CALLBACK . '?state=' . $this->validState() . '&code=abc');

        $this->postJson('/api/v1/members/login', [
            'email' => 'asha@gmail.com',
            'password' => 'anything',
        ])->assertStatus(401)
            ->assertJsonPath('type', 'INVALID_CREDENTIALS');
    }
}
