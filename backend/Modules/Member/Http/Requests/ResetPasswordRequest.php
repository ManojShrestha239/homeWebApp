<?php

declare(strict_types=1);

namespace Modules\Member\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Password;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

final class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            // Our tokens are always 64 characters (Str::random(64))
            'token' => ['required', 'string', 'size:64'],
            // 'confirmed' requires a matching password_confirmation field
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers(),
            ],
        ];
    }

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
