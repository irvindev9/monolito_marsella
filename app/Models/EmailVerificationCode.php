<?php

namespace App\Models;

use App\Mail\VerificationCodeMail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class EmailVerificationCode extends Model
{
    public const COOLDOWN_SECONDS = 600;
    public const EXPIRES_MINUTES = 15;

    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public static function throttleKey(User $user): string
    {
        return 'verify-code:' . $user->id;
    }

    public static function remainingCooldown(User $user): int
    {
        $key = self::throttleKey($user);

        return RateLimiter::tooManyAttempts($key, 1)
            ? RateLimiter::availableIn($key)
            : 0;
    }

    public static function hasActiveCode(User $user): bool
    {
        return self::where('user_id', $user->id)
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Issue and email a 4-digit code if not rate-limited.
     * Returns remaining cooldown seconds (> 0) if blocked, or 0 if sent.
     */
    public static function issueFor(User $user): int
    {
        if ($remaining = self::remainingCooldown($user)) {
            return $remaining;
        }

        self::where('user_id', $user->id)->delete();

        $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);

        self::create([
            'user_id' => $user->id,
            'code' => $code,
            'expires_at' => now()->addMinutes(self::EXPIRES_MINUTES),
        ]);

        RateLimiter::hit(self::throttleKey($user), self::COOLDOWN_SECONDS);

        if (config('mail.default') === 'resend' && empty(config('services.resend.key')) && empty(config('resend.api_key'))) {
            Log::warning('No se encontró la API key de Resend (RESEND_KEY); no se envió el código de verificación.', [
                'user_id' => $user->id,
            ]);
            return 0;
        }

        try {
            Mail::to($user->email)->send(new VerificationCodeMail($user, $code));
        } catch (\Throwable $e) {
            Log::error('Error al enviar código de verificación: ' . $e->getMessage(), [
                'user_id' => $user->id,
            ]);
        }

        return 0;
    }

    public static function verifyFor(User $user, string $code): bool
    {
        $record = self::where('user_id', $user->id)
            ->where('code', $code)
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $record) {
            return false;
        }

        $user->markEmailAsVerified();
        self::where('user_id', $user->id)->delete();
        RateLimiter::clear(self::throttleKey($user));

        return true;
    }
}
