<?php

declare(strict_types=1);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Shared\Exceptions\ApiExceptionRenderer;
use Shared\Http\Middleware\EnsureJsonRequest;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Every API request passes the JSON check first
        $middleware->api(prepend: [EnsureJsonRequest::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // STEP 1: Tell Laravel that API and JSON-asking requests should get JSON errors
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request, Throwable $e): bool => $request->is('api/*') || $request->expectsJson()
        );

        // STEP 2: Send every exception through our renderer.
        // Returning null means "not mine", so Laravel handles it normally.
        $exceptions->render(
            fn(Throwable $e, Request $request) => ApiExceptionRenderer::render($e, $request)
        );
    })
    ->create();
