import ApiService from "@/Modules/infrastructure/ApiService";
import DtoForm from "@/Modules/dto/DtoForm";

export default class Form extends ApiService {
    static model = DtoForm;
    private _apiIndex: string;
    private _apiRegistrationPost: string;

    constructor(response: DtoForm) {
        super(response);
;
        this._apiIndex = 'api.form.guest.index'
        this._apiRegistrationPost = 'api.form.registration.post';
    }

    async getIndex(params: any)
    {
        return await this.get(this._apiIndex, params);
    }

    async postIndex(params: any)
    {
        return await this.post(this._apiRegistrationPost, params);
    }

    get apiIndex(): string {
        return this._apiIndex;
    }

    set apiIndex(value: string) {
        this._apiIndex = value;
    }

    static createFields(): object
    {
        return {
            name: null,
            email: null,
            phone: null,
            sex: null,
            age: null,
            organization: null,
            is_ip: false,
            is_pwd: false,
            city_address: null,
            province_address: null,
            country_address: null,
            agreed_tc: false,
            event_id: null,
        }
    }

    static updateFields(): object
    {
        return {
            name: null,
            email: null,
            phone: null,
            sex: null,
            age: null,
            organization: null,
            is_ip: false,
            is_pwd: false,
            city_address: null,
            province_address: null,
            country_address: null,
            agreed_tc: false,
        }
    }
}
