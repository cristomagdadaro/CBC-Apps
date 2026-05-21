import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoPersonnelRegistration extends DtoBaseClass implements IPersonnelRegistration {
    fname: string;
    mname: string;
    lname: string;
    suffix: string;
    position: string;
    phone: string;
    address: string;
    email: string;
    employee_id: string;
    is_philrice_employee: boolean;
    status: string;
    email_verified_at: string | null;
    verification_sent_at: string | null;
    rejection_remarks: string | null;
    reviewed_by: string | null;
    reviewed_at: string | null;
    personnel_id: string | number | null;
    full_name: string;
    is_email_verified: boolean;

    constructor(data: IPersonnelRegistration) {
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
        this.is_philrice_employee = Boolean(data?.is_philrice_employee);
        this.status = data?.status ?? 'pending';
        this.email_verified_at = data?.email_verified_at ?? null;
        this.verification_sent_at = data?.verification_sent_at ?? null;
        this.rejection_remarks = data?.rejection_remarks ?? null;
        this.reviewed_by = data?.reviewed_by ?? null;
        this.reviewed_at = data?.reviewed_at ?? null;
        this.personnel_id = data?.personnel_id ?? null;
        this.full_name = data?.full_name ?? [this.fname, this.mname, this.lname, this.suffix].filter(Boolean).join(' ');
        this.is_email_verified = Boolean(data?.is_email_verified);

        this.api.setSearchFields({
            search: null,
            filter: null,
            filter_by: null,
            is_exact: false,
            page: 1,
            per_page: 15,
            sort: 'created_at',
            order: 'desc',
        });
    }
}
