import DtoSupplier from "@/Modules/dto/DtoSupplier";

export default class Supplier extends DtoSupplier {
    static endpoints = {
        index: 'api.inventory.suppliers.index',
        post: 'api.inventory.suppliers.store',
        put: 'api.inventory.suppliers.update',
        delete: 'api.inventory.suppliers.destroy',
        show: 'suppliers.show',
    };
    
    constructor(response: DtoSupplier) {
        super(response);

        this.api._apiIndex = Supplier.endpoints.index;
        this.api._apiPost = Supplier.endpoints.post;
        this.api._apiPut = Supplier.endpoints.put;
        this.api._apiDelete = Supplier.endpoints.delete;

        this.showPage = Supplier.endpoints.show;
    }

    createFields(): object {
        return {
            name: null,
            email: null,
            phone: null,
            address: null,
            description: null,
        }
    }

    updateFields(data: ISupplier): object
    {
        return {
            id: data?.id,
            name: data?.name,
            email: data?.email,
            phone: data?.phone,
            address: data?.address,
            description: data?.description,
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
                visible: true,
            }, {
                title: 'Name',
                key: 'name',
                db_key: 'name',
                align: 'text-center',
                sortable: true,
                visible: true,
            },{
                title: 'Email',
                key: 'email',
                db_key: 'email',
                align: 'text-center',
                sortable: true,
                visible: true,
            },{
                title: 'Phone',
                key: 'phone',
                db_key: 'phone',
                align: 'text-center',
                sortable: true,
                visible: true,
            },{
                title: 'Address',
                key: 'address',
                db_key: 'address',
                align: 'text-center',
                sortable: true,
                visible: true,
            },{
                title: 'Description',
                key: 'description',
                db_key: 'description',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
        ]
    }
}
