<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

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
        //
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof GSMConnectionNotFoundException) {
            return $exception->render($request);
        }

        return parent::render($request, $exception);
    }
}
