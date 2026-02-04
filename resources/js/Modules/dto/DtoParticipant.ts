import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoRegistration from "@/Modules/dto/DtoRegistration";

export default class DtoParticipant extends DtoBaseClass implements IParticipant {
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
    region_address: string;
    country_address: string;
    agreed_tc: string;
    agreed_updates: string;
    attendance_type?: string;

    registrations: IRegistration[];

    constructor(data: any) {
        super(data);

        this.name = data?.name ?? '';
        this.email = data?.email ?? '';
        this.phone = data?.phone ?? '';
        this.sex = data?.sex ?? '';
        this.age = data?.age ?? 0;
        this.organization = data?.organization ?? '';
        this.designation = data?.designation ?? '';
        this.is_ip = data?.is_ip ? "Yes" : "No";
        this.is_pwd = data?.is_pwd ? "Yes" : "No";
        this.city_address = data?.city_address ?? '';
        this.province_address = data?.province_address ?? '';
        this.region_address = data?.region_address ?? '';
        this.country_address = data?.country_address ?? '';
        this.agreed_tc = data?.agreed_tc ? "Yes" : "No";
        this.agreed_updates = data?.agreed_updates ? "Yes" : "No";

        this.registrations = Array.isArray(data?.registrations)
            ? data.registrations.map(i => new DtoRegistration(i))
            : [];
    }
}
