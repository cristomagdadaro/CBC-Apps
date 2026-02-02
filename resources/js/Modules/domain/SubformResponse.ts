import DtoSubformResponse from "@/Modules/dto/DtoSubformResponse";
import DtoBaseClass from "../dto/DtoBaseClass";

export default class SubformResponse extends DtoSubformResponse {
    static endpoints = {
        index: 'api.subform.response.index',
        post: 'api.subform.response.store',
        delete: 'api.subform.response.delete',
    };

    constructor(response: DtoSubformResponse) {
        super(response);
        
        this.api._apiIndex = SubformResponse.endpoints.index;
        this.api._apiPost = SubformResponse.endpoints.post;
        this.api._apiDelete = SubformResponse.endpoints.delete;
    }

    identifier(model: DtoBaseClass): object {
        if (model)
            return {
                id: model?.id
            };

        return {
            id: this?.id,
        }
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
            subform_type: null,
            form_parent_id: null,
            participant_id: null,
            response_data: {},
        }
    }

    updateFields(data: ISubformResponse): object
    {
        return {
            id: data?.id,
            subform_type: data?.subform_type,
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
                title: 'Form Parent ID',
                key: 'form_parent_id',
                db_key: 'form_parent_id',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Participant ID',
                key: 'participant_id',
                db_key: 'participant_id',
                align: 'center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Subform Type',
                key: 'subform_type',
                db_key: 'subform_type',
                align: 'center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Respondent',
                key: 'respondent_name',
                db_key: 'respondent_name',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Response Snapshot',
                key: 'response_preview',
                db_key: 'response_preview',
                align: 'text-left',
                sortable: false,
                visible: true,
            },
            {
                title: 'Submitted On',
                key: 'created_at',
                db_key: 'created_at',
                align: 'center',
                sortable: true,
                visible: true,
            },
        ]
    }
}
