<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Repositories\OptionRepo;

class RecipientResolver
{
    public function __construct(private readonly OptionRepo $optionRepo)
    {
    }

    public function resolve(string $domain): array
    {
        $domainConfig = config("notifications.domains.{$domain}", []);

        $emails = collect($this->emailsFromOptionKeys($domainConfig['option_keys'] ?? []));

        if ($emails->isEmpty()) {
            $emails = $emails->merge($this->emailsFromOptionKeys($domainConfig['fallback_option_keys'] ?? []));
        }

        $emails = $emails->merge($this->emailsFromRoles($domainConfig['roles'] ?? []));

        return $emails
            ->map(fn (string $email) => strtolower(trim($email)))
            ->filter(fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    private function emailsFromOptionKeys(array $keys): array
    {
        return collect($keys)
            ->flatMap(function (string $key) {
                return $this->resolveUserBackedEmails($this->optionRepo->getByKey($key));
            })
            ->all();
    }

    private function emailsFromRoles(array $roles): array
    {
        if (empty($roles)) {
            return [];
        }

        return User::query()
            ->whereNotNull('email')
            ->where(function ($query) use ($roles) {
                $query->where('is_admin', true)
                    ->orWhereHas('roles', function ($roleQuery) use ($roles) {
                        $roleQuery->whereIn('name', $roles);
                    });
            })
            ->pluck('email')
            ->filter()
            ->values()
            ->all();
    }

    private function resolveUserBackedEmails(mixed $value): array
    {
        $entries = collect($this->normalizeRecipientEntries($value));

        if ($entries->isEmpty()) {
            return [];
        }

        $userIds = $entries
            ->pluck('user_id')
            ->filter()
            ->map(fn ($userId) => (string) $userId)
            ->unique()
            ->values();

        $emails = collect();

        if ($userIds->isNotEmpty()) {
            $emails = $emails->merge(
                User::query()
                    ->whereIn('id', $userIds->all())
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->all()
            );
        }

        $emailEntries = $entries
            ->pluck('email')
            ->filter()
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->unique()
            ->values();

        if ($emailEntries->isNotEmpty()) {
            $emails = $emails->merge(
                User::query()
                    ->whereIn('email', $emailEntries->all())
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->all()
            );
        }

        return $emails
            ->map(fn ($email) => strtolower(trim((string) $email)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function normalizeRecipientEntries(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if (is_string($value)) {
            return collect(preg_split('/[\s,;]+/', $value) ?: [])
                ->filter()
                ->map(fn ($email) => ['email' => $email])
                ->values()
                ->all();
        }

        if (is_numeric($value)) {
            return [
                ['user_id' => (string) $value],
            ];
        }

        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->flatMap(function ($entry) {
                if (is_string($entry)) {
                    if (filter_var($entry, FILTER_VALIDATE_EMAIL)) {
                        return [['email' => $entry]];
                    }

                    return [['user_id' => trim($entry)]];
                }

                if (is_numeric($entry)) {
                    return [['user_id' => (string) $entry]];
                }

                if (is_array($entry)) {
                    $userId = $entry['user_id']
                        ?? $entry['id']
                        ?? (filter_var($entry['value'] ?? null, FILTER_VALIDATE_EMAIL) ? null : ($entry['value'] ?? null));
                    $email = $entry['email']
                        ?? (filter_var($entry['value'] ?? null, FILTER_VALIDATE_EMAIL) ? $entry['value'] : null);

                    return [[
                        'user_id' => $userId !== null ? (string) $userId : null,
                        'email' => $email !== null ? (string) $email : null,
                    ]];
                }

                return [];
            })
            ->filter(fn (array $entry) => !empty($entry['user_id']) || !empty($entry['email']))
            ->all();
    }
}
