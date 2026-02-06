export interface RentalVenue {
    id: string;
    venue_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    expected_attendees: number;
    event_name: string;
    requested_by: string;
    contact_number: string;
    status: 'pending' | 'approved' | 'rejected' | 'completed' | 'cancelled';
    notes?: string | null;
    created_at: string;
    updated_at: string;
}

export interface CreateRentalVenuePayload {
    venue_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    expected_attendees: number;
    event_name: string;
    requested_by: string;
    contact_number: string;
    notes?: string | null;
}

export interface RentalVenueListResponse {
    data: RentalVenue[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
}
