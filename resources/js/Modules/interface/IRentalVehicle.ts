interface IRentalVehicle extends IBaseClass {
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
    notes?: string;
}
