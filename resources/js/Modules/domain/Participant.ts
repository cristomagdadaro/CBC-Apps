import ApiService from "@/Modules/infrastructure/ApiService";
import DtoParticipant from "@/Modules/dto/DtoParticipant";

export default class Participant extends ApiService {
    static model = DtoParticipant;
    private _apiIndex: string;
    private _apiPost: string;

    constructor(response: DtoParticipant) {
        super(response);
        this._apiIndex = 'api.form.participants.index'
        this._apiPost = 'api.form.registration.post';
    }

    get apiIndex(): string {
        return this._apiIndex;
    }

    set apiIndex(value: string) {
        this._apiIndex = value;
    }

    async getIndex(params: any)
    {
        return await this.get(this._apiIndex, params);
    }

    async postIndex(params: any)
    {
        return await this.post(this._apiPost, params);
    }

    createFields(): object
    {
        return {
            name: null,
            email: null,
            phone: null,
            sex: null,
            age: null,
            organization: null,
            designation: null,
            is_ip: false,
            is_pwd: false,
            city_address: null,
            province_address: null,
            country_address: null,
            agreed_tc: false,
            event_id: null,
        }
    }

    updateFields(): object
    {
        return {
            name: null,
            email: null,
            phone: null,
            sex: null,
            age: null,
            organization: null,
            designation: null,
            is_ip: false,
            is_pwd: false,
            city_address: null,
            province_address: null,
            country_address: null,
            agreed_tc: false,
        }
    }
}
