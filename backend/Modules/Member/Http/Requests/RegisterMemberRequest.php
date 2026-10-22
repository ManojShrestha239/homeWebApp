<?php

declare(strict_types=1);

namespace Modules\Member\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

/**
 * Laravel runs these rules BEFORE the controller. If they fail, a 422 is returned automatically.
 */
final class RegisterMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // anyone may register
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            // 'confirmed' requires a matching field named password_confirmation
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ];
        // Note: we do NOT write 'unique:members,email' here. Two people can click at the same
        // second. The database unique index is the only fully safe guard, so the service handles it.
    }

    /**
     * Laravel calls this when validation fails. We reply with our own 422 receipt
     * and skip Laravel's default response.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::make(
                HttpStatus::UNPROCESSABLE_ENTITY,
                'VALIDATION_ERROR',
                'The given data was invalid.',
                null,
                ['errors' => $validator->errors()->toArray()],
            ),
        );
    }
}
