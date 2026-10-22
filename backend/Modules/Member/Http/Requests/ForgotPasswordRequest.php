<?php

declare(strict_types=1);

namespace Modules\Member\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

final class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
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
