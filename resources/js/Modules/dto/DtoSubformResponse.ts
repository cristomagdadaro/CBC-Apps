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
}
