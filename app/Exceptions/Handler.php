<?php

namespace App\Exceptions;

use Throwable;
use App\Enums\StatusCodeEnum;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected $overrideExceptions = [
        NotFoundHttpException::class => StatusCodeEnum::NOT_FOUND,
        UnprocessableEntityHttpException::class => StatusCodeEnum::UNPROCESSABLE_ENTITY,
        AccessDeniedHttpException::class => StatusCodeEnum::FORBIDDEN,
        AuthenticationException::class => StatusCodeEnum::UNAUTHORIZED
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

        $this->renderable(function (Exception $e) {
            $class = get_class($e);

            if (!isset($this->overrideExceptions[$class])) return;

            return Response::jsonError([], $e->getMessage(), $this->overrideExceptions[$class]);
        });
    }
}
