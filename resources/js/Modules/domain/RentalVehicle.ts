import DtoRentalVehicle from "@/Modules/dto/DtoRentalVehicle";

export default class RentalVehicle extends DtoRentalVehicle {
    static endpoints = {
        index: 'api.rental.vehicles.index',
        store: 'api.rental.vehicles.store',
        update: 'api.rental.vehicles.update',
        destroy: 'api.rental.vehicles.destroy',
        show: 'rental-vehicles.show',
    }
    constructor(response: DtoRentalVehicle) {
        super(response);
        this.api._apiIndex = RentalVehicle.endpoints.index;
        this.api._apiPost = RentalVehicle.endpoints.store;
        this.api._apiPut = RentalVehicle.endpoints.update;
        this.api._apiDelete = RentalVehicle.endpoints.destroy;
        this.showPage = RentalVehicle.endpoints.show;
    }

    createFields(): object {
        return {
            vehicle_type: null,
            date_from: null,
            date_to: null,
            time_from: null,
            time_to: null,
            purpose: null,
            requested_by: null,
            contact_number: null,
            status: 'pending',
            notes: null,
        };
    }

    updateFields(data: IRentalVehicle): object {
        return {
            id: data?.id,
            vehicle_type: data?.vehicle_type,
            date_from: data?.date_from,
            date_to: data?.date_to,
            time_from: data?.time_from,
            time_to: data?.time_to,
            purpose: data?.purpose,
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
                title: 'Vehicle Type',
                key: 'vehicle_type',
                db_key: 'vehicle_type',
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
                title: 'Purpose',
                key: 'purpose',
                db_key: 'purpose',
                align: 'text-left',
                sortable: true,
                visible: false,
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
