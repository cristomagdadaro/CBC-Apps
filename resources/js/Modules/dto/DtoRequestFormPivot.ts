import DtoBaseClass from "@/Modules/dto/DtoBaseClass";
import DtoRequester from "@/Modules/dto/DtoRequester";
import DtoRequestForm from "@/Modules/dto/DtoRequestForm";

export default class DtoRequestFormPivot extends DtoBaseClass implements IRequestFormPivot {
    requester_id: string;
    form_id: string;
    request_status: string;
    agreed_clause_1: string;
    agreed_clause_2: string;
    agreed_clause_3: string;
    approval_constraint: string;
    disapproved_remarks: string;
    approved_by: string;

    requester: IRequester;
    requestForm: IRequestForm;

    constructor(data: any) {
        super(data);

        this.requester_id = data?.requester_id;
        this.form_id = data?.form_id;
        this.request_status = data?.request_status;
        this.agreed_clause_1 = data?.agreed_clause_1;
        this.agreed_clause_2 = data?.agreed_clause_2;
        this.agreed_clause_3 = data?.agreed_clause_3;
        this.approval_constraint = data?.approval_constraint;
        this.disapproved_remarks = data?.disapproved_remarks;
        this.approved_by = data?.approved_by;

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
