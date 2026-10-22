<?php

declare(strict_types=1);

namespace Modules\Member\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Member\Models\Member;

/**
 * Decides EXACTLY which fields leave the server. The password is never listed, so it never leaks.
 *
 * @mixin Member
 */
final class MemberResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'email_verified' => $this->email_verified_at !== null,   // true/false, not the raw date
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
