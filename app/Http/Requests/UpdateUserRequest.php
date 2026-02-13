<?php

namespace App\Http\Requests;

use App\Enums\Role as RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id, 'id')],
            'employee_id' => ['nullable', 'string', 'max:64', Rule::unique('users', 'employee_id')->ignore($id, 'id')],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['sometimes', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in(RoleEnum::values())],
        ];
    }
}
