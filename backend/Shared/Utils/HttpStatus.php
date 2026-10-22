<?php

// Strict types: PHP refuses to silently convert "201" (text) into 201 (number)
declare(strict_types=1);

namespace Shared\Utils;

/**
 * The ONLY HTTP status codes our API may send.
 * Because this is an enum, a typo like `HttpStatus::CREAETD` is caught by the editor,
 * and a made-up code like 418 simply cannot be written.
 */
enum HttpStatus: int
{
    // ── Success ───────────────────────────────────────────
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NO_CONTENT = 204;

        // ── Client errors (the caller's fault) ────────────────
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case CONFLICT = 409;
    case UNSUPPORTED_MEDIA_TYPE = 415;
    case UNPROCESSABLE_ENTITY = 422;
    case TOO_MANY_REQUESTS = 429;

        // ── Server errors (our fault) ─────────────────────────
    case INTERNAL_SERVER_ERROR = 500;
    case NOT_IMPLEMENTED = 501;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;
    case GATEWAY_TIMEOUT = 504;
}
