<?php

declare(strict_types=1);

namespace Modules\Member\DTOs;

/**
 * A small, typed box for the login data, moving from the controller to the service.
 */
final readonly class LoginMemberDTO
{
    public function __construct(
        public string $email,
        public string $password,
    ) {}

    /**
     * @param array{email: string, password: string} $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            email: strtolower(trim($validated['email'])),   // match the registration rule
            password: $validated['password'],
        );
    }
}
