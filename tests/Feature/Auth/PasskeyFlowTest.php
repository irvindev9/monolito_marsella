<?php

namespace Tests\Feature\Auth;

use App\Models\EmailVerificationCode;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class PasskeyFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_time_login_redirects_to_passkey_prompt_and_remembers_dismissal(): void
    {
        $user = User::factory()->create([
            'passkey_prompted_at' => null,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('passkey.prompt'));

        $promptResponse = $this->actingAs($user)->get(route('passkey.prompt'));
        $promptResponse->assertOk();

        $dismissResponse = $this->actingAs($user)->post(route('passkey.dismiss'));
        $dismissResponse->assertRedirect(RouteServiceProvider::HOME);

        $this->assertNotNull($user->fresh()->passkey_prompted_at);

        // Subsequent visits to prompt redirect directly to HOME
        $this->actingAs($user->fresh())
            ->get(route('passkey.prompt'))
            ->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_subsequent_login_skips_passkey_prompt_after_choice_is_saved(): void
    {
        $user = User::factory()->create([
            'passkey_prompted_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_email_verification_code_redirects_new_user_to_passkey_prompt(): void
    {
        $user = User::factory()->unverified()->create([
            'passkey_prompted_at' => null,
        ]);

        EmailVerificationCode::issueFor($user);
        $code = EmailVerificationCode::where('user_id', $user->id)->value('code');

        $response = $this->actingAs($user)->post(route('verification.code'), [
            'code' => $code,
        ]);

        $response->assertRedirect(route('passkey.prompt'));
    }

    public function test_authenticated_user_can_request_webauthn_register_options_and_delete_credential(): void
    {
        $user = User::factory()->create();

        $optionsResponse = $this->actingAs($user)->postJson(route('webauthn.register.options'));
        $optionsResponse->assertOk()->assertJsonStructure(['challenge', 'rp', 'user']);

        $credential = $user->makeWebAuthnCredential([
            'id' => 'test-credential-id',
            'user_id' => Uuid::uuid4()->toString(),
            'alias' => 'Mi iPhone',
            'counter' => 0,
            'rp_id' => 'localhost',
            'origin' => 'http://localhost',
            'public_key' => '-----BEGIN PUBLIC KEY-----test-----END PUBLIC KEY-----',
        ]);
        $credential->save();

        $this->assertDatabaseHas('webauthn_credentials', [
            'id' => 'test-credential-id',
            'alias' => 'Mi iPhone',
        ]);

        $deleteResponse = $this->actingAs($user)->delete(route('webauthn.destroy', 'test-credential-id'));
        $deleteResponse->assertRedirect();

        $this->assertDatabaseMissing('webauthn_credentials', [
            'id' => 'test-credential-id',
        ]);
    }

    public function test_guest_can_request_webauthn_login_options(): void
    {
        $response = $this->postJson(route('webauthn.login.options'));

        $response->assertOk()->assertJsonStructure(['challenge']);
    }
}
