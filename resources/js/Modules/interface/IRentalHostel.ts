interface IRentalHostel extends IBaseClass {
    hostel_unit: string;
    check_in_date: string;
    check_out_date: string;
    number_of_guests: number;
    guest_name: string;
    contact_number: string;
    status: string;
    notes?: string;
}
