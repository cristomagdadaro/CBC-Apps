import DtoRentalVehicle from "@/Modules/dto/DtoRentalVehicle";

export default class RentalVehicle extends DtoRentalVehicle {
    static endpoints = {
        index: 'api.rental.vehicles.index',
        store: 'api.rental.vehicles.store',
        update: 'api.rental.vehicles.update',
        destroy: 'api.rental.vehicles.destroy',
        //show: 'rental-vehicles.show',
    }
    constructor(response: DtoRentalVehicle) {
        super(response);
        this.api._apiIndex = RentalVehicle.endpoints.index;
        this.api._apiPost = RentalVehicle.endpoints.store;
        this.api._apiPut = RentalVehicle.endpoints.update;
        this.api._apiDelete = RentalVehicle.endpoints.destroy;
        //this.showPage = RentalVehicle.endpoints.show;
    }

    createFields(): object {
        return {
            vehicle_type: null,
            trip_type: 'dedicated_trip',
            date_from: null,
            date_to: null,
            time_from: null,
            time_to: null,
            purpose: null,
            destination_location: null,
            destination_city: null,
            destination_province: null,
            destination_region: null,
            destination_stops: [],
            requested_by: null,
            members_of_party: [],
            is_shared_ride: false,
            shared_ride_reference: null,
            contact_number: null,
            status: 'pending',
            notes: null,
        };
    }

    updateFields(data: IRentalVehicle): object {
        return {
            id: data?.id,
            vehicle_type: data?.vehicle_type,
            trip_type: data?.trip_type,
            date_from: data?.date_from,
            date_to: data?.date_to,
            time_from: data?.time_from,
            time_to: data?.time_to,
            purpose: data?.purpose,
            destination_location: data?.destination_location,
            destination_city: data?.destination_city,
            destination_province: data?.destination_province,
            destination_region: data?.destination_region,
            destination_stops: data?.destination_stops ?? [],
            requested_by: data?.requested_by,
            members_of_party: data?.members_of_party ?? [],
            is_shared_ride: Boolean(data?.is_shared_ride),
            shared_ride_reference: data?.shared_ride_reference ?? null,
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
                visible: false,
            },
            {
                title: 'Vehicle Type',
                key: 'vehicle_type',
                db_key: 'vehicle_type',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Trip Workflow',
                key: 'trip_type',
                db_key: 'trip_type',
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
                title: 'Members of Party',
                key: 'members_of_party',
                db_key: 'members_of_party',
                align: 'text-left',
                sortable: false,
                visible: false,
            },
            {
                title: 'Contact Number',
                key: 'contact_number',
                db_key: 'contact_number',
                align: 'text-center',
                sortable: true,
                visible: false,
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
                title: 'Destination Region',
                key: 'destination_region',
                db_key: 'destination_region',
                align: 'text-left',
                sortable: true,
                visible: false,
            },
            {
                title: 'Destination Province',
                key: 'destination_province',
                db_key: 'destination_province',
                align: 'text-left',
                sortable: true,
                visible: false,
            },
            {
                title: 'Destination City',
                key: 'destination_city',
                db_key: 'destination_city',
                align: 'text-left',
                sortable: true,
                visible: false,
            },
            {
                title: 'Destination Location',
                key: 'destination_location',
                db_key: 'destination_location',
                align: 'text-left',
                sortable: true,
                visible: false,
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
                title: 'Purpose',
                key: 'purpose',
                db_key: 'purpose',
                align: 'text-left',
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
