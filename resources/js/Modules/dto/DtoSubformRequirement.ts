import DtoBaseClass from "@/Modules/dto/DtoBaseClass";

export default class DtoSubformRequirement extends DtoBaseClass implements ISubformRequirement {
    event_id: string;
    form_type: string;
    is_required: boolean;
    max_slots?: number;
    responses_count?: number;
    config: Record<string, any>;

    constructor(data: any) {
        super(data);

        this.event_id = data?.event_id ?? '';
        this.form_type = data?.form_type ?? '';
        this.is_required = data?.is_required ?? false;
        this.max_slots = data?.max_slots ?? null;
        this.responses_count = data?.responses_count ?? 0;
        this.config = data?.config ?? {};
    }
}
