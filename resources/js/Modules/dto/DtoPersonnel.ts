import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoPersonnel extends DtoBaseClass implements IPersonnel {
    fname: string;
    mname: string;
    lname: string;
    suffix: string;
    position: string;
    phone: string;
    address: string;
    email: string;
    employee_id: string;

    constructor(data: IPersonnel) {
        super(data);

        this.fname = data?.fname;
        this.mname = data?.mname;
        this.lname = data?.lname;
        this.suffix = data?.suffix;
        this.position = data?.position;
        this.phone = data?.phone;
        this.address = data?.address;
        this.email = data?.email;
        this.employee_id = data?.employee_id;

        // sorted by created_at desc
        this.api.setSearchFields({
            search: null,
            filter: null,
            filter_by: null,
            is_exact: false,
            page: 1,
            per_page: 50,
            sort: 'created_at',
            order: 'desc'
        });
    }
}
