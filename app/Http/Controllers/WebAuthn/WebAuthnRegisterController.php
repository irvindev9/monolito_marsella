<?php

namespace App\Http\Controllers\WebAuthn;

use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Laragear\WebAuthn\Http\Requests\AttestationRequest;
use Laragear\WebAuthn\Http\Requests\AttestedRequest;

use function response;

class WebAuthnRegisterController
{
    /**
     * Show the one-time passkey setup prompt screen.
     */
    public function prompt(Request $request): InertiaResponse|RedirectResponse
    {
        if (! $request->user()->needsPasskeyPrompt()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return Inertia::render('Auth/PasskeyPrompt');
    }

    /**
     * Remember that the user dismissed or skipped the passkey prompt.
     */
    public function dismissPrompt(Request $request): RedirectResponse
    {
        $request->user()->forceFill(['passkey_prompted_at' => now()])->save();

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Returns a challenge to be verified by the user device.
     */
    public function options(AttestationRequest $request): Responsable
    {
        return $request
            ->fastRegistration()
            ->toCreate();
    }

    /**
     * Registers a device for further WebAuthn authentication.
     */
    public function register(AttestedRequest $request): JsonResponse
    {
        $request->validate([
            'alias' => ['nullable', 'string', 'max:100'],
        ]);

        $id = $request->save([
            'alias' => $request->input('alias') ?: $this->guessDeviceName($request->userAgent()),
        ]);

        $request->user()->forceFill(['passkey_prompted_at' => now()])->save();

        return response()->json([
            'id' => $id,
            'redirect' => session()->pull('url.intended', RouteServiceProvider::HOME),
        ]);
    }

    /**
     * Remove a registered passkey belonging to the authenticated user.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $request->user()
            ->webAuthnCredentials()
            ->whereKey($id)
            ->delete();

        return back()->with('status', 'passkey-deleted');
    }

    private function guessDeviceName(?string $ua): string
    {
        if (! $ua) {
            return 'Dispositivo';
        }

        return match (true) {
            str_contains($ua, 'iPhone') => 'iPhone',
            str_contains($ua, 'iPad') => 'iPad',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Macintosh') || str_contains($ua, 'Mac OS') => 'Mac',
            str_contains($ua, 'Windows') => 'Windows',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Dispositivo',
        };
    }
}
