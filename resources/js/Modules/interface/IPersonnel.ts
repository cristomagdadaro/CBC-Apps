interface IPersonnel extends IBaseClass {
    fname: string;
    mname: string;
    lname: string;
    suffix: string;
    position: string;
    phone: string;
    address: string;
    email: string;
    employee_id: string;
    has_email?: boolean;
    profile_requires_update?: boolean;
    affiliation: string;
    status: string;
    expires_at: string;
    fullName: string;
}
