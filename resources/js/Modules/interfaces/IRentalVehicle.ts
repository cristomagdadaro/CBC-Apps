export interface RentalVehicle {
    id: string;
    vehicle_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    purpose: string;
    destination_location: string;
    destination_city: string;
    destination_province: string;
    destination_region: string;
    requested_by: string;
    members_of_party?: string[];
    contact_number: string;
    status: 'pending' | 'approved' | 'rejected' | 'completed' | 'cancelled';
    notes?: string | null;
    created_at: string;
    updated_at: string;
}

export interface CreateRentalVehiclePayload {
    vehicle_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    purpose: string;
    requested_by: string;
    members_of_party?: string[];
    contact_number: string;
    notes?: string | null;
}

export interface RentalVehicleListResponse {
    data: RentalVehicle[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
}
