<?php

namespace App\Enums;

enum Role: string
{
    case ADMIN = 'admin';
    case LABORATORY_MANAGER = 'laboratory_manager';
    case ICT_MANAGER = 'ict_manager';
    case ADMINISTRATIVE_ASSISTANT = 'administrative_assistant';
    case RESEARCHER = 'researcher';
    case RESEARCH_SUPERVISOR = 'research_supervisor';

    public static function values(): array
    {
        return array_map(static fn (self $role) => $role->value, self::cases());
    }
}
