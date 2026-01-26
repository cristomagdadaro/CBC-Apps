import DtoSubformResponse from "@/Modules/dto/DtoSubformResponse";

export default class SubformResponse extends DtoSubformResponse {
    static endpoints = {
        index: 'api.subform.response.index',
        post: 'api.subform.response.store',
    };

    constructor(response: DtoSubformResponse) {
        super(response);
        
        this.api._apiIndex = SubformResponse.endpoints.index;
        this.api._apiPost = SubformResponse.endpoints.post;
        this.api.appendWith = ['participant'];
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
            response_data: {
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
                event_id: null,
                attendance_type: null,
                agreed_tc: false,
            },
        }
    }

    getSubformFields(formtype: string): object
    {
        switch (formtype) {
            case 'preregistration':
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
                    event_id: null,
                    attendance_type: null,
                    agreed_tc: false,
                };
            case 'registration':
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
                    event_id: null,
                    attendance_type: null,
                    agreed_tc: false,
                };
            case 'preregistration_biotech':
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
                    event_id: null,
                    attendance_type: null,
                    join_quiz_bee: false,
                    agreed_tc: false,
                };
            case 'feedback':
                return {
                    clarity_objective: null,
                    time_allotment: null,
                    attainment_objective: null,
                    relevance_usefulness: null,
                    overall_quality_content: null,
                    overall_quality_resource_persons: null,
                    time_management_organization: null,
                    support_staff: null,
                    overall_quality_activity_admin: null,
                    knowledge_gain: null,
                    comments_event_coordination: '',
                    other_topics: '',
                    agreed_tc: false,
                };
            case 'posttest':
                return {
                    ...baseFields,
                };
            case 'pretest':
                return {
                    ...baseFields,
                };
            default:
                return {
                    ...baseFields,
                };
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
