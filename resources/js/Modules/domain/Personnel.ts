import DtoPersonnel from "@/Modules/dto/DtoPersonnel";

export default class Personnel extends DtoPersonnel {
    static endpoints = {
        index: 'api.inventory.personnels.index',
        post: 'api.inventory.personnels.store',
        put: 'api.inventory.personnels.update',
        delete: 'api.inventory.personnels.destroy',
        show: 'personnels.show',
    };

    constructor(response: DtoPersonnel) {
        super(response);
        this.api._apiIndex = Personnel.endpoints.index;
        this.api._apiPost = Personnel.endpoints.post;
        this.api._apiPut = Personnel.endpoints.put;
        this.api._apiDelete = Personnel.endpoints.delete;
    
        this.showPage = Personnel.endpoints.show;
    }

    createFields(): object
    {
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
        }
    }

    updateFields(data: IPersonnel): object
    {
        return {
            id: data?.id,
            is_philrice_employee: !/^CBC-\d{2}-\d{4}$/.test(data?.employee_id || ''),
            fname: data?.fname,
            mname:  data?.mname,
            lname: data?.lname,
            suffix: data?.suffix,
            position: data?.position,
            phone: data?.phone,
            address: data?.address,
            email: data?.email,
            employee_id: data?.employee_id,
        }
    }

    static getColumns() {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'PhilRice ID',
                key: 'employee_id',
                db_key: 'employee_id',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Name',
                key: 'fullName',
                db_key: 'fname',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Middle Name',
                key: 'mname',
                db_key: 'mname',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Last Name',
                key: 'lname',
                db_key: 'lname',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Suffix',
                key: 'suffix',
                db_key: 'suffix',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Position',
                key: 'position',
                db_key: 'position',
                align: 'text-left',
                sortable: true,
                visible: true,
            },
            {
                title: 'Phone',
                key: 'phone',
                db_key: 'phone',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
            {
                title: 'Address',
                key: 'address',
                db_key: 'address',
                align: 'text-center',
                sortable: true,
                visible: false,
            },
            {
                title: 'Email',
                key: 'email',
                db_key: 'email',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
           
        ]
    }
}
