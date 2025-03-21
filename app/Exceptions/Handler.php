<?php

namespace App\Exceptions;

use Throwable;
use App\Traits\ApiResposeTrait;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Illuminate\Http\Response;

class Handler extends ExceptionHandler
{
    use ApiResposeTrait;
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

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return $this->errorResponse('Unauthenticated', 401);
        }

          if ($exception instanceof UnauthorizedHttpException) {
        return $this->errorResponse('Unauthorized', 403);
    }

        if ($exception instanceof OAuthServerException) {
            return response()->json([
                'error' => 'Token Error',
                'message' => $exception->getMessage(),
            ], $exception->getHttpStatusCode());
        }

        if ($exception instanceof ThrottleRequestsException) {
            $message =  'لقد قمت بإجراء الكثير من المحاولات. يرجى المحاولة لاحقًا.';
            return $this->errorResponse($message, 429);


        }

        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => 'Unauthenticated. Token has expired or is invalid.',
                'status_code' => Response::HTTP_UNAUTHORIZED
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->errorResponse('Resource not found', 404);
        }
        if ($exception instanceof ValidationException) {
            $errors = $exception->validator->errors()->messages();

            $formattedErrors = [];
            foreach ($errors as $field => $messages) {
                $formattedErrors[$field] = array_values($messages)[0];
            }

            $response = [
                'message' => 'Validation failed.', // Customize message if needed
                'errors' => $formattedErrors,
            ];
            return $this->errorResponse($response, 422);
        }

        if ($exception instanceof NotFoundHttpException) {
            $message =  'The requested resource was not found.';
            return $this->errorResponse( $message , 404);
        }

        if ($exception instanceof \ErrorException && strpos($exception->getMessage(), 'Undefined variable') !== false) {
            $message = 'Undefined variable';
            return $this->errorResponse( $message , 500);

        }

            if ($exception instanceof \BadMethodCallException && strpos($exception->getMessage(), 'does not exist') !== false) {
                $message = 'Method does not exist';
                return $this->errorResponse($message , 500);

            }
        return parent::render($request, $exception);
    }
}
