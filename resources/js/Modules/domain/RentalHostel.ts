import DtoRentalHostel from "@/Modules/dto/DtoRentalHostel";

export default class RentalHostel extends DtoRentalHostel {
    static endpoints = {
        index: 'api.rental.hostels.index',
        store: 'api.rental.hostels.store',
        update: 'api.rental.hostels.update',
        destroy: 'api.rental.hostels.destroy',
        show: 'rental-hostels.show',
    }
    constructor(response: DtoRentalHostel) {
        super(response);
        
        this.api._apiIndex = RentalHostel.endpoints.index;
        this.api._apiPost = RentalHostel.endpoints.store;
        this.api._apiPut = RentalHostel.endpoints.update;
        this.api._apiDelete = RentalHostel.endpoints.destroy;
        this.showPage = RentalHostel.endpoints.show;
    }

    createFields(): object {
        return {
            hostel_unit: null,
            check_in_date: null,
            check_out_date: null,
            number_of_guests: null,
            guest_name: null,
            contact_number: null,
            status: 'pending',
            notes: null,
        };
    }

    updateFields(data: IRentalHostel): object {
        return {
            id: data?.id,
            hostel_unit: data?.hostel_unit,
            check_in_date: data?.check_in_date,
            check_out_date: data?.check_out_date,
            number_of_guests: data?.number_of_guests,
            guest_name: data?.guest_name,
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
                title: 'Hostel Unit',
                key: 'hostel_unit',
                db_key: 'hostel_unit',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Guest Name',
                key: 'guest_name',
                db_key: 'guest_name',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Number of Guests',
                key: 'number_of_guests',
                db_key: 'number_of_guests',
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
                title: 'Check-in Date',
                key: 'check_in_date',
                db_key: 'check_in_date',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Check-out Date',
                key: 'check_out_date',
                db_key: 'check_out_date',
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
