<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200)
            ->assertViewIs('auth.verify-email');
    }

    public function test_notice_redirects_if_verified_and_active(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_email_can_be_verified(): void
    {
        $user = User::factory()->unverified()->create();

        Event::fake();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(1440),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        Event::assertDispatched(Verified::class);
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertEquals('active', $user->fresh()->status);
        $response->assertRedirect(route('verification.success'));
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(1440),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertEquals('pending_verification', $user->fresh()->status);
        $response->assertViewIs('auth.verification-failed')
            ->assertViewHas('error');
    }

    public function test_verification_fails_with_expired_url(): void
    {
        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinutes(1), // Already expired
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $this->assertEquals('pending_verification', $user->fresh()->status);
        $response->assertViewIs('auth.verification-failed')
            ->assertViewHas('error');
    }

    public function test_middleware_redirects_unverified_users(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('verification.notice'));
    }

    public function test_middleware_redirects_inactive_users(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'status' => 'suspended',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('verification.notice'))
            ->assertSessionHas('error', 'Please verify your email address before accessing this feature.');
    }
}
