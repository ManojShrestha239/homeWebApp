<?php

declare(strict_types=1);

namespace Modules\Rbac\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\Member\Models\Member;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

final class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $member = $this->user();

        // Only members with this permission may submit the form
        return $member instanceof Member
            && $member->hasAnyPermission(['inventory.products.create']);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'max:64', 'unique:products,sku'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /** Return our 403 schema instead of Laravel's default response */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            ApiResponse::make(HttpStatus::FORBIDDEN, 'FORBIDDEN', 'You do not have permission to do that.'),
        );
    }
}
