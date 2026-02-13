<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserRepo extends AbstractRepoService
{
    public function __construct(User $model)
    {
        parent::__construct($model);
        $this->appendWith = ['roles:id,name,label'];
    }

    public function createWithRoles(array $data): Model
    {
        $payload = $this->extractUserPayload($data, true);

        $user = $this->create($payload);

        $this->syncRoles($user, $data['roles'] ?? []);

        return $user->fresh(['roles:id,name,label']);
    }

    public function updateWithRoles(string $id, array $data): Model
    {
        $payload = $this->extractUserPayload($data, false);

        $user = $this->update($id, $payload);

        if (array_key_exists('roles', $data)) {
            $this->syncRoles($user, $data['roles'] ?? []);
        }

        return $user->fresh(['roles:id,name,label']);
    }

    protected function extractUserPayload(array $data, bool $isCreate): array
    {
        $payload = [
            'name' => $data['name'] ?? null,
            'email' => $data['email'] ?? null,
            'employee_id' => $data['employee_id'] ?? null,
        ];

        if ($isCreate || array_key_exists('is_admin', $data)) {
            $payload['is_admin'] = (bool) ($data['is_admin'] ?? false);
        }

        if ($isCreate || !empty($data['password'])) {
            $payload['password'] = Hash::make((string) $data['password']);
        }

        return array_filter($payload, static fn ($value) => $value !== null);
    }

    protected function syncRoles(Model $user, array $roleNames): void
    {
        $roleIds = Role::query()
            ->whereIn('name', $roleNames)
            ->pluck('id')
            ->all();

        $user->roles()->sync($roleIds);
    }
}
