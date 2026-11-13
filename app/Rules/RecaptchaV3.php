<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaV3 implements ValidationRule
{
    public function __construct(
        private ?string $expectedAction = null,
        private float $minScore = 0.5,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $secret = config('services.recaptcha.secret_key');
        if (empty($secret)) {
            return;
        }

        if (empty($value) || ! is_string($value)) {
            $fail('No se pudo validar reCAPTCHA. Recarga la página e intenta de nuevo.');
            return;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $secret,
                'response' => $value,
            ]);

            $data = $response->json();
            $validAction = ! $this->expectedAction || (($data['action'] ?? null) === $this->expectedAction);

            if (! ($data['success'] ?? false) || ($data['score'] ?? 0) < $this->minScore || ! $validAction) {
                Log::warning('reCAPTCHA v3 failed', [
                    'data' => $data,
                    'expected_action' => $this->expectedAction,
                    'token_len' => strlen($value),
                ]);
                $fail('La verificación de seguridad (reCAPTCHA) falló. Intenta de nuevo.');
            }
        } catch (\Throwable $e) {
            report($e);
            $fail('Error al validar reCAPTCHA. Intenta de nuevo.');
        }
    }
}
