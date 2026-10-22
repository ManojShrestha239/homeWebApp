<?php

declare(strict_types=1);

namespace Modules\Member\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

final class LoginMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // anyone may try to log in
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
            // We don't enforce password strength at login. Old passwords must still work.
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],   // optional, defaults to false
        ];
    }

    /**
     * Same pattern as registration: reply with our own 422 receipt.
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
