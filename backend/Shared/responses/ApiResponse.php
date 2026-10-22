<?php

declare(strict_types=1);

namespace Shared\responses;

use Illuminate\Http\JsonResponse;
use Shared\Utils\HttpStatus;

/**
 * The single place that builds every API reply.
 * Guarantees the MANDATORY schema: code, status, type, message, description, data, meta.
 */
final class ApiResponse
{
    public static function make(
        HttpStatus $status,              // which code to send (must be from the enum)
        string $type,                    // short machine-readable label, e.g. "MEMBER_REGISTERED"
        string $message,                 // human-readable sentence for the user
        ?string $description = null,     // optional extra detail
        mixed $data = null,              // the payload (object, array, or null)
        ?string $nextCursor = null,      // for paginated lists later
        ?string $prevCursor = null,      // for paginated lists later
    ): JsonResponse {
        return response()->json(
            [
                'code' => $status->value,            // e.g. 201
                'status' => $status->value < 400,    // true for 2xx, false for errors
                'type' => $type,
                'message' => $message,
                'description' => $description,
                'data' => $data,
                'meta' => [
                    'next_cursor' => $nextCursor,    // always present, even when null
                    'prev_cursor' => $prevCursor,
                ],
            ],
            $status->value,                          // also set the real HTTP status line
        );
    }
}
