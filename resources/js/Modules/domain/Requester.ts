import DtoRequester from "@/Modules/dto/DtoRequester";

export default class Requester extends DtoRequester {
    static endpoints = {
        index: 'api.requester.guest.index',
        post: 'api.requester.post',
        put: 'api.requester.put',
        delete: 'api.requester.delete',
    };

    static model = DtoRequester;

    constructor(response: DtoRequester) {
        super(response);

        this.api._apiIndex = Requester.endpoints.index;
        this.api._apiPost = Requester.endpoints.post;
        this.api._apiPut = Requester.endpoints.put;
        this.api._apiDelete = Requester.endpoints.delete;
    }

    createFields(): object
    {
        return {
            name: null,
            affiliation: null,
            philrice_id: null,
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
            philrice_id: model.philrice_id ?? null,
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
                title: 'PhilRice ID',
                key: 'philrice_id',
                db_key: 'philrice_id',
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
