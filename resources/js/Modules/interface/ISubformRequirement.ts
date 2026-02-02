interface ISubformRequirement extends IBaseClass {
    event_id: string;
    form_type: string;
    is_required: boolean;
    max_slots?: number;
    responses_count?: number;
    config: Record<string, any>;
}
