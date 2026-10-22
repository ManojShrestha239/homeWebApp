<?php

declare(strict_types=1);

namespace Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects requests that send a body in the wrong format (for example, a form instead of JSON).
 */
final class EnsureJsonRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only methods that send a body need this check
        $sendsBody = in_array($request->method(), ['POST', 'PUT', 'PATCH'], true);

        if ($sendsBody && ! $request->isJson()) {
            throw new ApiException(
                HttpStatus::UNSUPPORTED_MEDIA_TYPE,            // 415
                'UNSUPPORTED_MEDIA_TYPE',
                'Send the request body as JSON.',
                'Add the header: Content-Type: application/json',
            );
        }

        // Everything looks good, continue to the controller
        return $next($request);
    }
}
