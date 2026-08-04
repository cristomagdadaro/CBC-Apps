interface IRequestFormPivot extends IBaseClass {
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
}
