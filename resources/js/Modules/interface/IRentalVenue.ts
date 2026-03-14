interface IRentalVenue extends IBaseClass {
    venue_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    expected_attendees: number;
    event_name: string;
    destination_location: string;
    destination_city: string;
    destination_province: string;
    destination_region: string;
    requested_by: string;
    contact_number: string;
    status: string;
    notes?: string;
}
