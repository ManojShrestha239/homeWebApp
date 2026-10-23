<?php

declare(strict_types=1);

namespace Modules\Rbac\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Modules\Rbac\Support\RbacCatalog;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

final class AssignMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;   // the route middleware already checked members.roles.assign
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            // Only roles from our catalog can be assigned
            'role' => ['required', 'string', Rule::in(RbacCatalog::roles())],
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
