<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

        $this->renderable(function (UnauthorizedException $e, $request) {
            return response()->json([
                'success'  => false,
                'message' => 'Unauthorized',
            ], 403);
        });

        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            return response()->json([
                'success'  => false,
                'message' => 'Access denied',
            ], 403);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json([
                'success'  => false,
                'message' => 'Not found',
            ], 404);
        });
    }

    protected function shouldReturnJson($request, Throwable $e)
    {
        return parent::shouldReturnJson($request, $e) || $request->is("api/*");
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is("api/*")) {
            return response()->json([
                'success' => false,
                'message' => 'Unathenticated'
            ], 401);
        }
    }
}
