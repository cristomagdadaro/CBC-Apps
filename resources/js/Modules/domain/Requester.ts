import ApiService from "@/Modules/infrastructure/ApiService";
import DtoRequester from "@/Modules/dto/DtoRequester";

export default class Requester extends ApiService {
    static model = DtoRequester;
    private _apiIndex: string;
    private _apiPost: string;
    private _apiPut: string;
    private _apiDelete: string;

    constructor(response: DtoRequester) {
        super(response);

        this._apiIndex = 'api.requester.guest.index';
        this._apiPost = 'api.requester.post';
        this._apiPut = 'api.requester.put';
        this._apiDelete = 'api.requester.delete';
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

    deleteField(model): object
    {
        return {
            id: model.id ?? null,
        };
    }

    static getFilterColumns() {
        return Requester.getColumns()
            .filter(column => column.visible !== false)
            .map(column => ({
                name: column.db_key,
                label: column.title,
            }));
    }

    createFields(): object
    {
        return {
            name: null,
            affiliation: null,
            email: null,
            position: null,
            phone: null,
        }
    }

    updateFields(model): object
    {
        return {
            id: model.id ?? null,
            name: model.name ?? null,
            affiliation: model.affiliation ?? null,
            email: model.email ?? null,
            position: model.position ?? null,
            phone: model.phone ?? null,
        }
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
                title: 'Name',
                key: 'name',
                db_key: 'name',
                align: 'left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Affiliation',
                key: 'affiliation',
                db_key: 'affiliation',
                align: 'left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Email',
                key: 'email',
                db_key: 'email',
                align: 'left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Position',
                key: 'position',
                db_key: 'position',
                align: 'left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Phone',
                key: 'phone',
                db_key: 'phone',
                align: 'left',
                sortable: true,
                visible: true,
            },
        ]
    }
}
