<?php

namespace App\Http\Requests;

use App\Enums\Role as RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'employee_id' => ['nullable', 'string', 'max:64', 'unique:users,employee_id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['sometimes', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in(RoleEnum::values())],
        ];
    }
}
