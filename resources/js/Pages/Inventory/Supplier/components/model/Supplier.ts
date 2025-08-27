import DtoSupplier from "@/Pages/Inventory/Supplier/components/model/DtoSupplier";

export default class Supplier extends DtoSupplier {
    constructor(response: DtoSupplier) {
        super(response);

        this.api._apiIndex = 'api.inventory.suppliers.index';
        this.api._apiPost = 'api.inventory.suppliers.store';
        this.api._apiPut = 'api.inventory.suppliers.update';
        this.api._apiDelete = 'api.inventory.suppliers.destroy';

        this.showPage = 'suppliers.show';
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
