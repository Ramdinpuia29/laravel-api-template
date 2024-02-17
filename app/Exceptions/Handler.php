<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;
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

        $this->renderable(function (HttpException $e, $request) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage();

            // if (env('APP_ENV') === 'production') {
            //     $this->logToTelegram($request, $message);
            // }

            return response()->json([
                'success' => false,
                'message' => $message
            ], $statusCode);
        });
    }

    protected function shouldReturnJson($request, Throwable $e)
    {
        return parent::shouldReturnJson($request, $e) || $request->is("api/*");
    }

    protected function unauthenticated($request, AuthenticationException $e)
    {
        if ($request->is("api/*")) {
            $message = $e->getMessage();

            // if (env('APP_ENV') === 'production') {
            //     $this->logToTelegram($request, $message);
            // }

            return response()->json([
                'success' => false,
                'message' => $message
            ], 401);
        }
    }

    public function logToTelegram($request, $message)
    {
        $url = $request->fullUrl();
        $host = $request->schemeAndHttpHost();
        $method = $request->method();
        $ipAddress = $request->ip();

        $text = "URL: {$url}, HOST: {$host}, METHOD: {$method}, IP ADDRESS: {$ipAddress}, MESSAGE: {$message}";
        Log::channel('telegram')->alert($text);
    }
}
