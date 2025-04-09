interface IParticipant extends IBaseClass {
    id: string;
    name: string;
    email: string;
    phone: string;
    sex: string;
    age: number;
    organization: string;
    designation: string;
    is_ip: boolean;
    is_pwd: boolean;
    city_address: string;
    province_address: string;
    country_address: string;
    agreed_tc: boolean;

    registrations: Array<IRegistration>
}
