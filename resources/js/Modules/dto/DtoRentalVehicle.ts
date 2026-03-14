import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoRentalVehicle extends DtoBaseClass implements IRentalVehicle {
    vehicle_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    purpose: string;
    requested_by: string;
    members_of_party: string[];
    contact_number: string;
    status: string;
    notes: string;

    constructor(data: IRentalVehicle) {
        super(data);

        this.vehicle_type = data?.vehicle_type;
        this.date_from = data?.date_from;
        this.date_to = data?.date_to;
        this.time_from = data?.time_from;
        this.time_to = data?.time_to;
        this.purpose = data?.purpose;
        this.requested_by = data?.requested_by;
        this.members_of_party = Array.isArray(data?.members_of_party) ? data.members_of_party : [];
        this.contact_number = data?.contact_number;
        this.status = data?.status || 'pending';
        this.notes = data?.notes;
    }
}
