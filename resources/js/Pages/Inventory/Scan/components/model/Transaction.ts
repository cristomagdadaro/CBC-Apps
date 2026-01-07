import DtoTransaction from "@/Pages/Inventory/Scan/components/model/DtoTransaction";
import {usePage} from "@inertiajs/vue3";

export default class Transaction extends DtoTransaction {
    constructor(response: DtoTransaction) {
    super(response);

    const page = usePage();

        this.api._apiIndex = 'api.inventory.transactions.index';
        // @ts-ignore
        this.api._apiPost = (page.props.auth && page.props.auth.user) ? 'api.inventory.transactions.store' : 'api.inventory.transactions.store.public';
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
            employee_id: null,
            user_id: null,
            expiration: null,
            remarks: null,
        };
    }

    updateFields(model: ITransaction): object
    {console.log(model);
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
            employee_id: model.employee_id ?? null,
            user_id: model.user_id ?? null,
            expiration: model.expiration ?? null,
            remarks: model.remarks ?? null,
        };
    }

    get dataColor( ){
        return `${this.transac_type && this.transac_type === 'incoming' ? 'text-green-600' : 'text-red-600'} text-center uppercase`;
    }

    static getColumns(data: DtoTransaction = null): any {
        return [
            {
                title: 'ID',
                key: 'id',
                db_key: 'id',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'Item',
                key: 'item.fullName',
                db_key: 'item',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'Barcode',
                key: 'barcode',
                db_key: 'barcode',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'Type',
                key: 'transac_type',
                db_key: 'transac_type',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'Quantity',
                key: 'quantityWithUnit',
                db_key: 'quantity',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'Unit',
                key: 'unit',
                db_key: 'unit',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'Unit Price',
                key: 'unit_price',
                db_key: 'unit_price',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'Total Cost',
                key: 'total_cost',
                db_key: 'total_cost',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'Expiration',
                key: 'expiration',
                db_key: 'expiration',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'User',
                key: 'user.fullName',
                db_key: 'user',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'Personnel',
                key: 'personnel.fullName',
                db_key: 'personnel_id',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'Date and Time',
                key: 'created_at',
                db_key: 'created_at',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'Remarks',
                key: 'remarks',
                db_key: 'remarks',
                align: 'dataColor',
                sortable: true,
                visible: true,
            }
        ]
    }
}
