import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoSubformResponse extends DtoBaseClass implements ISubformResponse {
    subform_type: string;
    form_parent_id: string;
    participant_id: string;
    response_data: Record<string, any>;

    constructor(data: any) {
        super(data);

        this.subform_type = data?.subform_type ?? '';
        this.form_parent_id = data?.form_parent_id ?? '';
        this.participant_id = data?.participant_id ?? '';
        this.response_data = data?.response_data ?? {};
    }

    static getSubformFields(subformType?: string): object
    {
        switch (subformType) {
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
                    
                };
            case 'pretest':
                return {
                };
            default:
                return {
                };
        }
    }
}
