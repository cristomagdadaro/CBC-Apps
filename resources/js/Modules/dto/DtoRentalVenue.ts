import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoRentalVenue extends DtoBaseClass implements IRentalVenue {
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
    notes: string;

    constructor(data: IRentalVenue) {
        super(data);

        this.venue_type = data?.venue_type;
        this.date_from = data?.date_from;
        this.date_to = data?.date_to;
        this.time_from = data?.time_from;
        this.time_to = data?.time_to;
        this.expected_attendees = data?.expected_attendees;
        this.event_name = data?.event_name;
        this.requested_by = data?.requested_by;
        this.contact_number = data?.contact_number;
        this.status = data?.status || 'pending';
        this.notes = data?.notes;
    }
}
