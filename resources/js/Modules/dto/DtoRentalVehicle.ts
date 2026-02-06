<?php

namespace App\Modules\dto;

use App\Modules\domain\RentalVehicle;

class DtoRentalVehicle
{
    public string $id;
    public string $vehicle_type;
    public string $date_from;
    public string $date_to;
    public string $time_from;
    public string $time_to;
    public string $purpose;
    public string $requested_by;
    public string $contact_number;
    public string $status;
    public ?string $notes;
    public string $created_at;
    public string $updated_at;

    public function __construct(DtoRentalVehicle|array $data = [])
    {
        if (is_array($data)) {
            $this->id = $data['id'] ?? '';
            $this->vehicle_type = $data['vehicle_type'] ?? '';
            $this->date_from = $data['date_from'] ?? '';
            $this->date_to = $data['date_to'] ?? '';
            $this->time_from = $data['time_from'] ?? '';
            $this->time_to = $data['time_to'] ?? '';
            $this->purpose = $data['purpose'] ?? '';
            $this->requested_by = $data['requested_by'] ?? '';
            $this->contact_number = $data['contact_number'] ?? '';
            $this->status = $data['status'] ?? 'pending';
            $this->notes = $data['notes'] ?? null;
            $this->created_at = $data['created_at'] ?? '';
            $this->updated_at = $data['updated_at'] ?? '';
        } else {
            $this->id = $data->id ?? '';
            $this->vehicle_type = $data->vehicle_type ?? '';
            $this->date_from = $data->date_from ?? '';
            $this->date_to = $data->date_to ?? '';
            $this->time_from = $data->time_from ?? '';
            $this->time_to = $data->time_to ?? '';
            $this->purpose = $data->purpose ?? '';
            $this->requested_by = $data->requested_by ?? '';
            $this->contact_number = $data->contact_number ?? '';
            $this->status = $data->status ?? 'pending';
            $this->notes = $data->notes ?? null;
            $this->created_at = $data->created_at ?? '';
            $this->updated_at = $data->updated_at ?? '';
        }
    }

    public function data(): array
    {
        return [
            'id' => $this->id,
            'vehicle_type' => $this->vehicle_type,
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'time_from' => $this->time_from,
            'time_to' => $this->time_to,
            'purpose' => $this->purpose,
            'requested_by' => $this->requested_by,
            'contact_number' => $this->contact_number,
            'status' => $this->status,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
