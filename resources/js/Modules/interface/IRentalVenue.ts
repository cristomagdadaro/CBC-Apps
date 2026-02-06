interface IRentalVenue extends IBaseClass {
    venue_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    expected_attendees: number;
    event_name: string;
    requested_by: string;
    contact_number: string;
    status: string;
    notes?: string;
}
