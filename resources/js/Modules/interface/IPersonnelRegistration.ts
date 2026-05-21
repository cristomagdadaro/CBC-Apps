interface IPersonnelRegistration extends IBaseClass {
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
}
