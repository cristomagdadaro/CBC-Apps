interface ISubformRequirement extends IBaseClass {
    event_id: string;
    form_type: string;
    is_required: boolean;
    config: Record<string, any>;
}
