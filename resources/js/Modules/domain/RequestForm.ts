import DtoRequestForm from "@/Modules/dto/DtoRequestForm";

export class RequestForm extends DtoRequestForm {
    constructor(response: DtoRequestForm) {
        super(response);

        this.api._apiIndex = 'api.requestForm.index';
        this.api._apiPost = 'api.requestForm.post';

        this.api.appendWith = ['requester'];
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
