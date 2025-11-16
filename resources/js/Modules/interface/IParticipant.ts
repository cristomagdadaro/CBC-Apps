interface IParticipant extends IBaseClass {
    id: string;
    name: string;
    email: string;
    phone: string;
    sex: string;
    age: number;
    organization: string;
    designation: string;
    is_ip: string;
    is_pwd: string;
    city_address: string;
    province_address: string;
    country_address: string;
    agreed_tc: string;
    event_id?: string;
    attendance_type?: string;

    registrations: Array<IRegistration>
}
