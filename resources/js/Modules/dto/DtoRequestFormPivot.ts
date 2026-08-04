import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoRequester from "@/Modules/dto/DtoRequester";
import DtoRequestForm from "@/Modules/dto/DtoRequestForm";

export default class DtoRequestFormPivot extends DtoBaseClass implements IRequestFormPivot {
    requester_id: string;
    form_id: string;
    request_status: string;
    agreed_clause_1: boolean;
    agreed_clause_2: boolean;
    agreed_clause_3: boolean;
    approval_constraint: string;
    disapproved_remarks: string;
    approved_by: string;
    approved_at: string | null;
    released_by: string;
    released_at: string | null;
    returned_by: string;
    returned_at: string | null;
    overdue_notified_at: string | null;
    display_status: string;
    is_overdue: boolean;
    schedule_end_at: string | null;
    next_action_label: string | null;

    requester?: IRequester;
    requestForm?: IRequestForm;

    constructor(data: any) {
        super(data);

        this.requester_id = data?.requester_id ?? '';
        this.form_id = data?.form_id ?? '';
        this.request_status = data?.request_status ?? 'pending';
        this.agreed_clause_1 = Boolean(data?.agreed_clause_1);
        this.agreed_clause_2 = Boolean(data?.agreed_clause_2);
        this.agreed_clause_3 = Boolean(data?.agreed_clause_3);
        this.approval_constraint = data?.approval_constraint ?? '';
        this.disapproved_remarks = data?.disapproved_remarks ?? '';
        this.approved_by = data?.approved_by ?? '';
        this.approved_at = data?.approved_at ?? null;
        this.released_by = data?.released_by ?? '';
        this.released_at = data?.released_at ?? null;
        this.returned_by = data?.returned_by ?? '';
        this.returned_at = data?.returned_at ?? null;
        this.overdue_notified_at = data?.overdue_notified_at ?? null;
        this.display_status = data?.display_status ?? this.request_status;
        this.is_overdue = Boolean(data?.is_overdue);
        this.schedule_end_at = data?.schedule_end_at ?? null;
        this.next_action_label = data?.next_action_label ?? null;

        if (data?.requester) {
            this.requester = new DtoRequester(data.requester);
        }

        if (data?.request_form) {
            this.requestForm = new DtoRequestForm(data.request_form);
        }

        this.api.setSearchFields({
            search: null,
            filter: null,
            filter_by: null,
            is_exact: false,
            page: 1,
            per_page: 10,
            sort: 'created_at',
            order: 'desc'
        });
    }
}
