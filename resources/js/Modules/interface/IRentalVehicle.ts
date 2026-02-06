interface IRentalVehicle extends IBaseClass {
    vehicle_type: string;
    date_from: string;
    date_to: string;
    time_from: string;
    time_to: string;
    purpose: string;
    requested_by: string;
    contact_number: string;
    status: string;
    notes?: string;
}
