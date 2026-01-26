import DtoSubformRequirement from "@/Modules/dto/DtoSubformRequirement";

export default class SubformRequirement extends DtoSubformRequirement {
    static endpoints = {
        index: 'api.subform.requirement.index',
    };

    constructor(response: DtoSubformRequirement) {
        super(response);
        
        this.api._apiIndex = SubformRequirement.endpoints.index;
        this.api.appendWith = ['responses']
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
                key: 'form_type',
                db_key: 'form_type',
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
