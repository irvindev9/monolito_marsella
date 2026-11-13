<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|Response
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        if (! EmailVerificationCode::hasActiveCode($user) && EmailVerificationCode::remainingCooldown($user) === 0) {
            EmailVerificationCode::issueFor($user);
        }

        return Inertia::render('Auth/VerifyEmail', [
            'status' => session('status'),
            'cooldownSeconds' => EmailVerificationCode::remainingCooldown($user),
            'email' => $user->email,
        ]);
    }
}
