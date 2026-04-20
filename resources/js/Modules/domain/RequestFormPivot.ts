import DtoRequestFormPivot from "@/Modules/dto/DtoRequestFormPivot";

export default class RequestFormPivot extends DtoRequestFormPivot {
    static endpoints = {
        index: 'api.requestFormPivot.index',
        post: 'api.requestFormPivot.post',
        put: 'api.requestFormPivot.put',
    };

    constructor(response: DtoRequestFormPivot) {
        super(response);
        
        this.api._apiIndex = RequestFormPivot.endpoints.index;
        this.api._apiPost = RequestFormPivot.endpoints.post;
        this.api._apiPut = RequestFormPivot.endpoints.put;

        this.api.appendWith = ['requester', 'request_form'];
    }

    createFields(): object {
        return {
            name: null,
            affiliation: null,
            requester_philrice_id: null,
            email: null,
            position: null,
            phone: null,

            request_type: [],
            request_details: null,
            request_purpose: null,
            project_title: null,
            date_of_use: null,
            date_of_use_end: null,
            time_of_use: null,
            time_of_use_end: null,
            labs_to_use: [],
            equipments_to_use: [],
            consumables_to_use: [],

            agreed_clause_1: false,
            agreed_clause_2: false,
            agreed_clause_3: false,
        }
    }

    updateFields(data: IRequestFormPivot): object {
        return {
            id: data?.id,
            request_status: data?.request_status,
            approval_constraint: data?.approval_constraint,
            disapproved_remarks: data?.disapproved_remarks,
            released_by: data?.released_by,
            released_at: data?.released_at,
            returned_by: data?.returned_by,
            returned_at: data?.returned_at,
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
                key: 'requester.fullName',
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
                title: 'Disapproved Remarks',
                key: 'disapproved_remarks',
                db_key: 'disapproved_remarks',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Reviewed By',
                key: 'approved_by',
                db_key: 'approved_by',
                align: 'center',
                sortable: true,
                visible: false,
            },
        ]
    }
}
