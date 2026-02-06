import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoRentalHostel extends DtoBaseClass implements IRentalHostel {
    hostel_unit: string;
    check_in_date: string;
    check_out_date: string;
    number_of_guests: number;
    guest_name: string;
    contact_number: string;
    status: string;
    notes: string;

    constructor(data: IRentalHostel) {
        super(data);

        this.hostel_unit = data?.hostel_unit;
        this.check_in_date = data?.check_in_date;
        this.check_out_date = data?.check_out_date;
        this.number_of_guests = data?.number_of_guests;
        this.guest_name = data?.guest_name;
        this.contact_number = data?.contact_number;
        this.status = data?.status || 'pending';
        this.notes = data?.notes;
    }
}
