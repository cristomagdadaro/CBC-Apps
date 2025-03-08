import ApiService from "@/Modules/infrastructure/ApiService";
import DtoForm from "@/Modules/dto/DtoForm";

export default class Form extends ApiService {
    static model = DtoForm;
    private _apiIndex: string;
    private _apiPost: string;

    constructor(response: DtoForm) {
        super(response);

        this._apiIndex = 'api.form.guest.index';
        this._apiPost = 'api.form.post';
    }

    async getIndex(params: any)
    {
        return await this.get(this._apiIndex, params);
    }

    get apiIndex(): string {
        return this._apiIndex;
    }

    set apiIndex(value: string) {
        this._apiIndex = value;
    }

    async postIndex(params: any)
    {
        return await this.post(this._apiPost, params);
    }

    static createFields(): object
    {
        return {
            event_id: null,
            title: null,
            description: null,
            details: null,
            date_from: null,
            date_to: null,
            time_from: null,
            time_to: null,
            venue: null,
            has_pretest: false,
            has_posttest: false,
            has_preregistration: false,
        }
    }

    static updateFields(data: IForm): object
    {
        return {
            event_id: data.event_id ?? null,
            title: data.title ?? null,
            description: data.description ?? null,
            details: data.details ?? null,
            date_from: data.date_from ?? null,
            date_to: data.date_to ?? null,
            time_from: data.time_from ?? null,
            time_to: data.time_to ?? null,
            venue: data.venue ?? null,
            has_pretest: data.has_pretest ?? null,
            has_posttest: data.has_posttest ?? null,
            has_preregistration: data.has_preregistration ?? null,
        }
    }
}
