import DtoRentalVenue from "@/Modules/dto/DtoRentalVenue";

export default class RentalVenue extends DtoRentalVenue {
    constructor(response: DtoRentalVenue) {
        super(response);
        this.api._apiIndex = 'api.rental.venues.index';
        this.api._apiPost = 'api.rental.venues.store';
        this.api._apiPut = 'api.rental.venues.update';
        this.api._apiDelete = 'api.rental.venues.destroy';

        this.showPage = 'rental-venues.show';
    }

    createFields(): object {
        return {
            venue_type: null,
            date_from: null,
            date_to: null,
            time_from: null,
            time_to: null,
            expected_attendees: null,
            event_name: null,
            requested_by: null,
            contact_number: null,
            status: 'pending',
            notes: null,
        };
    }

    updateFields(data: IRentalVenue): object {
        return {
            id: data?.id,
            venue_type: data?.venue_type,
            date_from: data?.date_from,
            date_to: data?.date_to,
            time_from: data?.time_from,
            time_to: data?.time_to,
            expected_attendees: data?.expected_attendees,
            event_name: data?.event_name,
            requested_by: data?.requested_by,
            contact_number: data?.contact_number,
            status: data?.status,
            notes: data?.notes,
        };
    }

    static getColumns() {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Venue Type',
                key: 'venue_type',
                db_key: 'venue_type',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Event Name',
                key: 'event_name',
                db_key: 'event_name',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Requested By',
                key: 'requested_by',
                db_key: 'requested_by',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Expected Attendees',
                key: 'expected_attendees',
                db_key: 'expected_attendees',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Contact Number',
                key: 'contact_number',
                db_key: 'contact_number',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Date From',
                key: 'date_from',
                db_key: 'date_from',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Date To',
                key: 'date_to',
                db_key: 'date_to',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Status',
                key: 'status',
                db_key: 'status',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Notes',
                key: 'notes',
                db_key: 'notes',
                align: 'text-left',
                sortable: false,
                visible: false,
            },
        ];
    }
}
