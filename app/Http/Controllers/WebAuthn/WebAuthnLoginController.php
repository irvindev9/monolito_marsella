<?php

namespace App\Http\Controllers\WebAuthn;

use App\Providers\RouteServiceProvider;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Laragear\WebAuthn\Http\Requests\AssertedRequest;
use Laragear\WebAuthn\Http\Requests\AssertionRequest;

use function response;

class WebAuthnLoginController
{
    /**
     * Returns the challenge to assertion.
     */
    public function options(AssertionRequest $request): Responsable
    {
        return $request->toVerify($request->validate(['email' => 'sometimes|nullable|email|string']));
    }

    /**
     * Log the user in.
     */
    public function login(AssertedRequest $request): JsonResponse
    {
        $user = $request->login();

        if (! $user) {
            return response()->json([
                'message' => 'No se pudo iniciar sesión con esta Passkey.',
            ], 422);
        }

        if (is_null($user->passkey_prompted_at)) {
            $user->forceFill(['passkey_prompted_at' => now()])->save();
        }

        $redirect = ! $user->hasVerifiedEmail()
            ? route('verification.notice')
            : session()->pull('url.intended', RouteServiceProvider::HOME);

        return response()->json(['redirect' => $redirect]);
    }
}
