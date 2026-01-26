import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoSubformRequirement extends DtoBaseClass implements ISubformRequirement {
    event_id: string;
    form_type: string;
    is_required: boolean;
    config: Record<string, any>;

    constructor(data: any) {
        super(data);

        this.event_id = data?.event_id ?? '';
        this.form_type = data?.form_type ?? '';
        this.is_required = data?.is_required ?? false;
        this.config = data?.config ?? {};
    }
}
