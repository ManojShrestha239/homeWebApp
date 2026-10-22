<?php

declare(strict_types=1);

namespace Modules\Member\DTOs;

final readonly class LoginMemberDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public bool $remember = false,
    ) {}

    /**
     * @param array{email: string, password: string, remember?: bool} $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            email: strtolower(trim($validated['email'])),
            password: $validated['password'],
            remember: (bool) ($validated['remember'] ?? false),
        );
    }
}
