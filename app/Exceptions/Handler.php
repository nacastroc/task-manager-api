<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        /*
        |--------------------------------------------------------------------------
        | Exception render handle
        |--------------------------------------------------------------------------
        |
        | Override the default render funtion since, being a dedicated API, no
        | HTML render is required.
        |
        */

        // Default values assume an internal server error.
        $statusCode = 500;
        $data = ['message' => config('constants.messages.http_500')];

        if ($exception instanceof ValidationException) {
            $statusCode = $exception->status;
            $data = [
                'message' => $exception->getMessage(),
                'errors' => $exception->errors()
            ];
        }
        else if ($exception instanceof AuthenticationException) {
            $statusCode = 401;
            $data = ['message' => config('constants.messages.http_401')];
        }
        else if ($exception instanceof HttpException) {
            $statusCode = $exception->getStatusCode();
            $data['message'] = Response::$statusTexts[$statusCode] ?? config('constants.messages.http_400');
        }
        // Add more exception types via else if clauses as needed.

        // Return a structured JSON response
        return response()->json($data, $statusCode);
    }
}
