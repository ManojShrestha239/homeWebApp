<?php

declare(strict_types=1);

namespace Shared\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Converts EVERY error on API routes into our JSON schema.
 * Called once from bootstrap/app.php.
 */
final class ApiExceptionRenderer
{
    public static function render(Throwable $e, Request $request): ?JsonResponse
    {
        // Only API URLs get JSON. Returning null lets Laravel handle normal web pages.
        if (! $request->is('api/*') || $request->expectsJson()) {
            return null;
        }

        // match(true) checks each condition from top to bottom and uses the first one that is true
        return match (true) {
            // Our own errors already know their status, type and message
            $e instanceof ApiException => ApiResponse::make($e->status, $e->type, $e->getMessage(), $e->description),

            // 422: the form data was wrong. The field-by-field errors go inside data.errors
            $e instanceof ValidationException => ApiResponse::make(
                HttpStatus::UNPROCESSABLE_ENTITY,
                'VALIDATION_ERROR',
                'The given data was invalid.',
                null,
                ['errors' => $e->errors()],
            ),

            $e instanceof AuthenticationException => ApiResponse::make(HttpStatus::UNAUTHORIZED, 'UNAUTHENTICATED', 'Please log in first.'),
            $e instanceof AuthorizationException => ApiResponse::make(HttpStatus::FORBIDDEN, 'FORBIDDEN', 'You are not allowed to do that.'),

            // 404: the URL or the record does not exist
            $e instanceof ModelNotFoundException, $e instanceof NotFoundHttpException
            => ApiResponse::make(HttpStatus::NOT_FOUND, 'NOT_FOUND', 'The requested resource was not found.'),

            $e instanceof MethodNotAllowedHttpException
            => ApiResponse::make(HttpStatus::METHOD_NOT_ALLOWED, 'METHOD_NOT_ALLOWED', 'That HTTP method is not allowed here.'),

            $e instanceof ThrottleRequestsException
            => ApiResponse::make(HttpStatus::TOO_MANY_REQUESTS, 'TOO_MANY_REQUESTS', 'Too many attempts. Please wait a moment.'),

            // Anything unexpected becomes a safe 500
            default => self::serverError($e),
        };
    }

    private static function serverError(Throwable $e): JsonResponse
    {
        return ApiResponse::make(
            HttpStatus::INTERNAL_SERVER_ERROR,
            'SERVER_ERROR',
            'Something went wrong on our side.',
            // Never reveal internal details in production. Only show them when APP_DEBUG=true
            config('app.debug') ? $e->getMessage() : null,
        );
    }
}
