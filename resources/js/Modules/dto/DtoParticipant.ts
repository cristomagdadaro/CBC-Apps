import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoRegistration from "@/Modules/dto/DtoRegistration";

export default class DtoParticipant extends DtoBaseClass implements IParticipant {
    id: string;
    name: string;
    email: string;
    phone: string;
    sex: string;
    age: number;
    organization: string;
    is_ip: boolean;
    is_pwd: boolean;
    city_address: string;
    province_address: string;
    country_address: string;
    agreed_tc: boolean;

    registrations: Array<IRegistration>

    table: string;
    created_at: Date;
    updated_at: Date;
    delete_at: Date;

    constructor(data: any) {
        super(data);

        this.id = data.id;
        this.name = data.name;
        this.email = data.email;
        this.phone = data.phone;
        this.sex = data.sex;
        this.age = data.age;
        this.organization = data.organization;
        this.is_ip = data.is_ip;
        this.is_pwd = data.is_pwd;
        this.city_address = data.city_address;
        this.province_address = data.province_address;
        this.country_address = data.city_address;
        this.agreed_tc = data.agreed_tc;

        if (data.registrations) {
            this.registrations = data.registrations.map(i => {
                return new DtoRegistration(i);
            })
        }
    }
}
