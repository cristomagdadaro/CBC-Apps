<?php

namespace App\Support\Broadcasting;

class ChannelNames
{
    public static function staffDashboard(): string
    {
        return 'staff.dashboard';
    }

    public static function inventoryTransactions(): string
    {
        return 'inventory.transactions';
    }

    public static function inventoryCheckout(): string
    {
        return 'inventory.checkout';
    }

    public static function inventoryItems(): string
    {
        return 'inventory.items';
    }

    public static function inventorySuppliers(): string
    {
        return 'inventory.suppliers';
    }

    public static function inventoryPersonnels(): string
    {
        return 'inventory.personnels';
    }

    public static function laboratoryLogs(): string
    {
        return 'laboratory.logs';
    }

    public static function ictLogs(): string
    {
        return 'ict.logs';
    }

    public static function equipmentUser(string $employeeId): string
    {
        return "equipment.user.{$employeeId}";
    }

    public static function rentalsCalendar(): string
    {
        return 'rentals.calendar';
    }

    public static function rentalsVehicles(): string
    {
        return 'rentals.vehicles';
    }

    public static function rentalsVenues(): string
    {
        return 'rentals.venues';
    }

    public static function formsEvent(string $eventId): string
    {
        return "forms.event.{$eventId}";
    }

    public static function certificatesBatch(string $batchId): string
    {
        return "certificates.batch.{$batchId}";
    }

    public static function publicRentalsCalendar(): string
    {
        return 'public.rentals.calendar';
    }

    public static function publicInventoryStock(): string
    {
        return 'public.inventory.stock';
    }

    public static function researchSamples(): string
    {
        return 'research.samples';
    }
}
