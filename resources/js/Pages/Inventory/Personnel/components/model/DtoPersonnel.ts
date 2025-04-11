import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoPersonnel extends DtoBaseClass implements IPersonnel {
    id: string;
    fname: string;
    mname: string;
    lname: string;
    suffix: string;
    position: string;
    phone: string;
    address: string;
    email: string;

    constructor(data: IPersonnel) {
        super(data);

        this.table = data.table;

        this.id = data.id;
        this.fname = data.fname;
        this.mname = data.mname;
        this.lname = data.lname;
        this.suffix = data.suffix;
        this.position = data.position;
        this.phone = data.phone;
        this.address = data.address;
        this.email = data.email;
    }
}
