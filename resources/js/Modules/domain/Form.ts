import ApiService from "@/Modules/infrastructure/ApiService";
import DtoForm from "@/Modules/dto/DtoForm";

export default class Form extends ApiService {
    static model = DtoForm;
    private _apiIndex: string;
    private _apiPost: string;
    private _apiPut: string;
    private _apiDelete: string;

    constructor(response: DtoForm) {
        super(response);

        this._apiIndex = 'api.form.guest.index';
        this._apiPost = 'api.form.post';
        this._apiPut = 'api.form.put';
        this._apiDelete = 'api.form.delete';
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

    async putIndex(params: any)
    {
        return await this.put(this._apiPut, params.event_id, params);
    }

    async postIndex(params: any)
    {
        return await this.post(this._apiPost, params);
    }

    async deleteApiIndex(params: any)
    {
        return await this.delete(this._apiDelete, params.event_id, params);
    }

    getFields(): object
    {
        return {
            search: null,
            filter: null,
            is_exact: false,
            page: 1,
            per_page: 10,
            sort: 'created_at',
            order: 'desc',
        }
    }

    deleteField(model): object
    {
        return {
            event_id: model.event_id ?? null,
        };
    }

    createFields(): object
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
            max_slots: null,
        }
    }

    updateFields(data: IForm): object
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
            is_suspended: data.is_suspended ?? null,
            max_slots: data.max_slots ?? null,
        }
    }

    static getFilterColumns() {
        return Form.getColumns()
            .filter(column => column.visible !== false)
            .map(column => ({
                name: column.db_key,
                label: column.title,
            }));
    }


    static getColumns()
    {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Event ID',
                key: 'event_id',
                db_key: 'event_id',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Title',
                key: 'title',
                db_key: 'title',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Description',
                key: 'description',
                db_key: 'description',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Details',
                key: 'details',
                db_key: 'details',
                align: 'center',
                sortable: true,
                visible: true,
            },
        ]
    }
}
