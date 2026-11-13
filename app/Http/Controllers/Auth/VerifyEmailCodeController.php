<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VerifyEmailCodeController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $request->validate([
            'code' => ['required', 'digits:4'],
        ], [
            'code.required' => 'Ingresa el código de 4 dígitos.',
            'code.digits' => 'El código debe tener exactamente 4 dígitos.',
        ]);

        if (! EmailVerificationCode::verifyFor($user, $request->code)) {
            throw ValidationException::withMessages([
                'code' => 'El código es incorrecto o ha expirado.',
            ]);
        }

        return redirect()->intended(RouteServiceProvider::HOME)->with('status', 'Correo verificado exitosamente.');
    }
}
