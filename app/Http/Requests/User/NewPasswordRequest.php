<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;

/**
 * @OA\Schema(
 *     schema="NewPasswordRequest",
 *     type="object",
 *     required={"token","email","password","password_confirmation"},
 *     @OA\Property(property="token", type="string", example="abc123def456", description="Password reset token"),
 *     @OA\Property(property="email", type="string", format="email", example="john.smith@example.com", description="User email"),
 *     @OA\Property(property="password", type="string", format="password", minLength=8, example="newPassword123", description="New password"),
 *     @OA\Property(property="password_confirmation", type="string", format="password", example="newPassword123", description="Confirm new password")
 * )
 */
class NewPasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }
}
