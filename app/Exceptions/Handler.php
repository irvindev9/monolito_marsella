<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if ($response->getStatusCode() === 419) {
            if ($request->expectsJson() && ! $request->header('X-Inertia')) {
                return response()->json(['message' => 'Tu sesión expiró, recargando...'], 419);
            }

            return back()->with([
                'status' => 'La página expiró por inactividad, por favor intenta de nuevo.',
            ]);
        }

        return $response;
    }
}
