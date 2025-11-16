import DtoSubformResponse from "@/Modules/dto/DtoSubformResponse";

export default class SubformResponse extends DtoSubformResponse {
    constructor(response: DtoSubformResponse) {
        super(response);

        this.api._apiIndex = 'api.subform.response.index';
        this.api._apiPost = 'api.subform.response.store';
    }

    deleteField(model): object
    {
        return {
            id: model?.id,
        };
    }

    createFields(): object
    {
        return {
            form_parent_id: null,
            participant_id: null,
            response_data: null,
        }
    }

    updateFields(data: ISubformResponse): object
    {
        return {
            form_parent_id: data?.form_parent_id,
            participant_id: data?.participant_id,
            response_data: data?.response_data,
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
