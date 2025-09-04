import DtoTransaction from "@/Pages/Inventory/Scan/components/model/DtoTransaction";

export default class Transaction extends DtoTransaction {
       constructor(response: DtoTransaction) {
        super(response);

        this.api._apiIndex = 'api.inventory.transactions.index';
        this.api._apiPost = 'api.inventory.transactions.store';
        this.api._apiPut = 'api.inventory.transactions.update';
        this.api._apiDelete = 'api.inventory.transactions.destroy';

        this.api.appendWith = ['item', 'user','personnel'];

        this.showPage = 'transactions.show';
    }

    createFields(): object
    {
        return {
            barcode: null,
            item_id: null,
            transac_type: null,
            quantity: null,
            unit: null,
            unit_price: null,
            total_cost: null,
            personnel_id: null,
            project_code: null,
            user_id: null,
            expiration: null,
            remarks: null,
        };
    }

    updateFields(model: ITransaction): object
    {
        return {
            id: model.id ?? null,
            barcode: model.barcode ?? null,
            item_id: model.item_id ?? null,
            transac_type: model.transac_type ?? null,
            quantity: model.quantity ?? null,
            unit: model.unit ?? null,
            unit_price: model.unit_price ?? null,
            total_cost: model.total_cost ?? null,
            personnel_id: model.personnel_id ?? null,
            project_code: model.project_code ?? null,
            user_id: model.user_id ?? null,
            expiration: model.expiration ?? null,
            remarks: model.remarks ?? null,
        };
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
            },{
                title: 'Item',
                key: 'item.fullName',
                db_key: 'item',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Barcode',
                key: 'barcode',
                db_key: 'barcode',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Type',
                key: 'transac_type',
                db_key: 'transac_type',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Quantity',
                key: 'quantity',
                db_key: 'quantity',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Unit',
                key: 'unit',
                db_key: 'unit',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Unit Price',
                key: 'unit_price',
                db_key: 'unit_price',
                align: 'center',
                sortable: true,
                visible: false,
            },{
                title: 'Total Cost',
                key: 'total_cost',
                db_key: 'total_cost',
                align: 'center',
                sortable: true,
                visible: false,
            },{
                title: 'Project Code',
                key: 'project_code',
                db_key: 'project_code',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Expiration',
                key: 'expiration',
                db_key: 'expiration',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Remarks',
                key: 'remarks',
                db_key: 'remarks',
                align: 'center',
                sortable: true,
                visible: false,
            },{
                title: 'User',
                key: 'user.fullName',
                db_key: 'user',
                align: 'center',
                sortable: true,
                visible: false,
            },{
                title: 'Personnel',
                key: 'personnel.fullName',
                db_key: 'personnel_id',
                align: 'center',
                sortable: true,
                visible: true,
            },{
                title: 'Date and Time',
                key: 'created_at',
                db_key: 'created_at',
                align: 'text-center',
                sortable: true,
                visible: true,
            },
        ]
    }
}
