<?php

namespace App\Services\Notifications;

class NotificationPreferenceService
{
    public function isEnabled(string $domain): bool
    {
        if (!config('notifications.enabled', true)) {
            return false;
        }

        return (bool) config("notifications.domains.{$domain}.enabled", true);
    }

    public function queueFor(string $domain, string $fallback = 'notifications'): string
    {
        return (string) config("notifications.domains.{$domain}.queue", $fallback);
    }

    public function deliveryModeFor(string $domain): string
    {
        $mode = (string) config("notifications.domains.{$domain}.delivery_mode", 'individual');

        return in_array($mode, ['individual', 'grouped'], true) ? $mode : 'individual';
    }

    public function groupedToFor(string $domain): array
    {
        $address = (string) config("notifications.domains.{$domain}.grouped_to.address", config('notifications.grouped.to.address', ''));
        $name = (string) config("notifications.domains.{$domain}.grouped_to.name", config('notifications.grouped.to.name', ''));

        return [
            'address' => trim($address),
            'name' => trim($name),
        ];
    }

    public function groupedChunkSizeFor(string $domain): ?int
    {
        $value = config("notifications.domains.{$domain}.grouped_chunk_size", config('notifications.grouped.chunk_size'));

        if ($value === null || $value === '') {
            return null;
        }

        $size = (int) $value;

        return $size > 0 ? $size : null;
    }
}
