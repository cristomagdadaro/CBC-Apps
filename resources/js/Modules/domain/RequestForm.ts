import DtoRequestForm from "@/Modules/dto/DtoRequestForm";

export class RequestForm extends DtoRequestForm {
    static endpoints = {
        index: 'api.requestForm.index',
        post: 'api.requestForm.post',
    };

    constructor(response: DtoRequestForm) {
        super(response);

        this.api._apiIndex = RequestForm.endpoints.index;
        this.api._apiPost = RequestForm.endpoints.post;

        this.api.appendWith = ['requester'];
    }

    createFields(): object
    {
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
            date_of_use_end: null,
            time_of_use: null,
            time_of_use_end: null,
            labs_to_use: [],
            equipments_to_use: [],
            consumables_to_use: [],
        }
    }

    updateFields(data: IRequestForm): object
    {
        return {
            id: data?.id,
            request_type: data?.request_type,
            request_details: data?.request_details,
            request_purpose: data?.request_purpose,
            project_title: data?.project_title,
            date_of_use: data?.date_of_use,
            date_of_use_end: data?.date_of_use_end,
            time_of_use: data?.time_of_use,
            time_of_use_end: data?.time_of_use_end,
            labs_to_use: data?.labs_to_use,
            equipments_to_use: data?.equipments_to_use,
            consumables_to_use: data?.consumables_to_use,
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
                title: 'Type',
                key: 'request_type',
                db_key: 'request_type',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Details',
                key: 'request_details',
                db_key: 'request_details',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Purpose',
                key: 'request_purpose',
                db_key: 'request_purpose',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Project Title',
                key: 'project_title',
                db_key: 'project_title',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Requested Date',
                key: 'date_of_use',
                db_key: 'date_of_use',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Requested Time',
                key: 'time_of_use',
                db_key: 'time_of_use',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Labs to Use',
                key: 'labs_to_use',
                db_key: 'labs_to_use',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Equipments to Use',
                key: 'equipments_to_use',
                db_key: 'equipments_to_use',
                align: 'center',
                sortable: true,
                visible: false,
            }, {
                title: 'Consumables to Use',
                key: 'consumables_to_use',
                db_key: 'consumables_to_use',
                align: 'center',
                sortable: true,
                visible: false,
            },
        ]
    }
}
