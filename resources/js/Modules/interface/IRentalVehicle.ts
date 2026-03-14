interface IRentalVehicle extends IBaseClass {
    vehicle_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    purpose: string;
    destination_location: string;
    destination_city: string;
    destination_province: string;
    destination_region: string;
    requested_by: string;
    members_of_party: string[];
    contact_number: string;
    status: string;
    notes?: string;
}
