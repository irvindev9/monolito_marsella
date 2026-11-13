<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\EmailVerificationCode;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        $remaining = EmailVerificationCode::issueFor($user);

        if ($remaining > 0) {
            $minutes = (int) ceil($remaining / 60);

            return back()->withErrors([
                'resend' => "Debes esperar {$minutes} min antes de reenviar el código.",
            ]);
        }

        return back()->with('status', 'verification-code-sent');
    }
}
