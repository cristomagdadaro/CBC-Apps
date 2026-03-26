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

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Admin',
            self::LABORATORY_MANAGER => 'Laboratory Manager',
            self::ICT_MANAGER => 'ICT Manager',
            self::ADMINISTRATIVE_ASSISTANT => 'Administrative Assistant',
            self::RESEARCHER => 'Researcher',
            self::RESEARCH_SUPERVISOR => 'Research Supervisor',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::ADMIN => 'Full system access',
            self::LABORATORY_MANAGER => 'Laboratory operations and inventory management',
            self::ICT_MANAGER => 'ICT operations, forms, and inventory access',
            self::ADMINISTRATIVE_ASSISTANT => 'Administrative support for rentals and certificates',
            self::RESEARCHER => 'Research project contributor',
            self::RESEARCH_SUPERVISOR => 'Research oversight and export approvals',
        };
    }

    public static function values(): array
    {
        return array_map(static fn (self $role) => $role->value, self::cases());
    }

    public static function defaults(): array
    {
        return array_map(static fn (self $role) => [
            'name' => $role->value,
            'label' => $role->label(),
            'description' => $role->description(),
        ], self::cases());
    }
}
