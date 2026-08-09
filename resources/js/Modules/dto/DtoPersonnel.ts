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
    affiliation: string;
    has_email?: boolean;
    profile_requires_update?: boolean;
    status: string;
    expires_at: string;

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
        this.affiliation = data?.affiliation;
        this.has_email = data?.has_email;
        this.profile_requires_update = data?.profile_requires_update;
        this.status = data?.status;
        this.expires_at = data?.expires_at;

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
