import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoRentalVehicle extends DtoBaseClass implements IRentalVehicle {
    vehicle_type: string | null;
    trip_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    purpose: string;
    destination_location: string;
    destination_city: string;
    destination_province: string;
    destination_region: string;
    destination_stops: string[];
    requested_by: string;
    members_of_party: string[];
    is_shared_ride: boolean;
    shared_ride_reference: string | null;
    contact_number: string;
    status: string;
    notes: string;

    constructor(data: IRentalVehicle) {
        super(data);

        this.vehicle_type = data?.vehicle_type;
        this.trip_type = data?.trip_type || 'dedicated_trip';
        this.date_from = data?.date_from;
        this.date_to = data?.date_to;
        this.time_from = data?.time_from;
        this.time_to = data?.time_to;
        this.purpose = data?.purpose;
        this.destination_location = data?.destination_location;
        this.destination_city = data?.destination_city;
        this.destination_province = data?.destination_province;
        this.destination_region = data?.destination_region;
        this.destination_stops = Array.isArray(data?.destination_stops) ? data.destination_stops : [];
        this.requested_by = data?.requested_by;
        this.members_of_party = Array.isArray(data?.members_of_party) ? data.members_of_party : [];
        this.is_shared_ride = Boolean(data?.is_shared_ride);
        this.shared_ride_reference = data?.shared_ride_reference ?? null;
        this.contact_number = data?.contact_number;
        this.status = data?.status || 'pending';
        this.notes = data?.notes;
    }
}
