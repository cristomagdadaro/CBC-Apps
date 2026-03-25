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
}
