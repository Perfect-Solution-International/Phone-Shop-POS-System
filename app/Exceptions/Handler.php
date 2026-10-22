<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = ['current_password', 'password', 'password_confirmation'];

    protected function shouldReturnJson(mixed $request, Throwable $exception): bool
    {
        return $request->is('api/*') || parent::shouldReturnJson($request, $exception);
    }

    public function register(): void {}
}
