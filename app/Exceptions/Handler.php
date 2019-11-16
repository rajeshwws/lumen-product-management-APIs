<?php

namespace App\Exceptions;

use App\Components\CustomException;
use App\Components\ErrorMessage;
use App\Components\Response;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof CustomException) {
            return Response::error($exception->getSpecialCode(), $exception->getMessage(), $exception->getData());
        } else if ($exception instanceof NotFoundHttpException){
            return Response::error(ErrorMessage::ROUTE_NOT_FOUND, $exception->getMessage(), null, 404);
        } else if ($exception instanceof ValidationException) {
            $error_message = $exception->validator->messages()->first();
            return Response::error(ErrorMessage::INVALID_INPUT, $error_message, null, 500);
        } else {
            return Response::error(ErrorMessage::INTERNAL_ERROR, $exception->getMessage(), null, 500);
        }

        return parent::render($request, $exception);
    }
}
