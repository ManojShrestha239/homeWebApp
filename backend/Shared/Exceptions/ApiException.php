<?php

declare(strict_types=1);

namespace Shared\Exceptions;

use RuntimeException;
use Shared\Utils\HttpStatus;
use Throwable;

/**
 * A "known" error we throw on purpose, e.g. "email already taken".
 * The renderer turns it into a clean JSON reply automatically.
 */
final class ApiException extends RuntimeException
{
    public function __construct(
        public readonly HttpStatus $status,      // which HTTP code to reply with
        public readonly string $type,            // machine label, e.g. "EMAIL_ALREADY_REGISTERED"
        string $message,                         // safe sentence shown to the user
        public readonly ?string $description = null,
        ?Throwable $previous = null,             // keeps the original error for logs
    ) {
        // Pass the message and numeric code to the normal PHP exception parent
        parent::__construct($message, $status->value, $previous);
    }
}
