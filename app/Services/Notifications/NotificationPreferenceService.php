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
}
