import DtoRequestFormPivot from "@/Modules/dto/DtoRequestFormPivot";

export default class RequestFormPivot extends DtoRequestFormPivot {
    constructor(response: DtoRequestFormPivot) {
        super(response);

        this.api._apiIndex = 'api.requestFormPivot.index';
        this.api._apiPost = 'api.requestFormPivot.post';
        this.api._apiPut = 'api.requestFormPivot.put';

        this.api.appendWith = ['requester', 'request_form'];
    }

    createFields(): object {
        return {
            name: null,
            affiliation: null,
            email: null,
            position: null,
            phone: null,

            request_type: [],
            request_details: null,
            request_purpose: null,
            project_title: null,
            date_of_use: null,
            time_of_use: null,
            labs_to_use: [],
            equipments_to_use: [],
            consumables_to_use: [],

            agreed_clause_1: null,
            agreed_clause_2: null,
            agreed_clause_3: null,
        }
    }

    updateFields(data: IRequestFormPivot): object {
        return {
            id: data?.id,
            requester_id: data?.requester_id,
            form_id: data?.form_id,
            request_status: data?.request_status,
            agreed_clause_1: data?.agreed_clause_1,
            agreed_clause_2: data?.agreed_clause_2,
            agreed_clause_3: data?.agreed_clause_3,
            approval_constraint: data?.approval_constraint,
            disapproved_remarks: data?.disapproved_remarks,
            approved_by: data?.approved_by,
        }
    }

    static getColumns() {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Requester',
                key: 'requester',
                db_key: 'requester',
                align: 'center',
                sortable: true,
                visible: true,
            }, {
                title: 'Form ID',
                key: 'form_id',
                db_key: 'form_id',
                align: 'center',
                sortable: true,
                visible: true,
            }, {
                title: 'Request Status',
                key: 'request_status',
                db_key: 'request_status',
                align: 'center',
                sortable: true,
                visible: true,
            }, {
                title: 'Agreed Clause 1',
                key: 'agreed_clause_1',
                db_key: 'agreed_clause_1',
                align: 'center',
                sortable: true,
                visible: true,
            }, {
                title: 'Agreed Clause 2',
                key: 'agreed_clause_2',
                db_key: 'agreed_clause_2',
                align: 'center',
                sortable: true,
                visible: true,
            }, {
                title: 'Agreed Clause 3',
                key: 'agreed_clause_3',
                db_key: 'agreed_clause_3',
                align: 'center',
                sortable: true,
                visible: true,
            }, {
                title: 'Disapproved Remarks',
                key: 'disapproved_remarks',
                db_key: 'disapproved_remarks',
                align: 'center',
                sortable: true,
                visible: true,
            }, {
                title: 'Reviewed By',
                key: 'approved_by',
                db_key: 'approved_by',
                align: 'center',
                sortable: true,
                visible: true,
            },
        ]
    }
}
