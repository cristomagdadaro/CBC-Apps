import DtoPersonnelRegistration from "@/Modules/dto/DtoPersonnelRegistration";

export default class PersonnelRegistration extends DtoPersonnelRegistration {
    static endpoints = {
        index: 'api.inventory.personnel-registrations.index',
        postGuest: 'api.inventory.personnel-registrations.store.guest',
        status: 'api.inventory.personnel-registrations.update-status',
    };

    constructor(response: DtoPersonnelRegistration = null) {
        super(response);

        this.api._apiIndex = PersonnelRegistration.endpoints.index;
        this.api._apiPost = PersonnelRegistration.endpoints.postGuest;
    }

    createFields(): object {
        return {
            is_philrice_employee: true,
            fname: null,
            mname: null,
            lname: null,
            suffix: null,
            position: null,
            phone: null,
            address: null,
            email: null,
            employee_id: null,
        };
    }

    static getColumns() {
        return [
            { title: 'Name', key: 'full_name', db_key: 'fname', align: 'text-left', sortable: true, visible: true },
            { title: 'Email', key: 'email', db_key: 'email', align: 'text-left', sortable: true, visible: true },
            { title: 'Employee ID', key: 'employee_id', db_key: 'employee_id', align: 'text-center', sortable: true, visible: true },
            { title: 'Status', key: 'status', db_key: 'status', align: 'text-center', sortable: true, visible: true },
            { title: 'Verified', key: 'is_email_verified', db_key: 'email_verified_at', align: 'text-center', sortable: true, visible: true },
        ];
    }

    static getFilterColumns() {
        return this.getColumns();
    }
}
