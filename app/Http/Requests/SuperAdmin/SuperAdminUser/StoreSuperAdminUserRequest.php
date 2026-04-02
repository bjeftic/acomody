<?php

namespace App\Http\Requests\SuperAdmin\SuperAdminUser;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuperAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'unique:users,email'],
        ];
    }
}
