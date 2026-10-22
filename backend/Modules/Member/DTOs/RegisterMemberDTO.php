<?php

declare(strict_types=1);

namespace Modules\Member\DTOs;

/**
 * DTO = Data Transfer Object: a small, typed "box" that carries the registration data
 * from the controller to the service. `readonly` means nobody can change it after it's created.
 */
final readonly class RegisterMemberDTO
{
    public function __construct(
        public string $fullName,
        public string $email,
        public string $password,
    ) {}

    /**
     * @param array{full_name: string, email: string, password: string} $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            fullName: $validated['full_name'],
            email: strtolower(trim($validated['email'])),   // "Asha@Mail.com" and "asha@mail.com" are the same person
            password: $validated['password'],
        );
    }
}
