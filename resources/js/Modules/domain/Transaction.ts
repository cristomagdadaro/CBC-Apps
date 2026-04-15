import DtoTransaction from "@/Modules/dto/DtoTransaction";
import {usePage} from "@inertiajs/vue3";

export default class Transaction extends DtoTransaction {
    static endpoints = {
        index: 'api.inventory.transactions.index',
        indexGuest: 'api.inventory.transactions.index.public',
        postAuth: 'api.inventory.transactions.store',
        postGuest: 'api.inventory.transactions.store.public',
        put: 'api.inventory.transactions.update',
        delete: 'api.inventory.transactions.destroy',
        show: 'transactions.show',
    };

    static page = usePage();

    private static currentUserId(): string | null {
        const authUser = (Transaction.page.props as any)?.auth?.user;
        return authUser?.id ?? null;
    }

    constructor(response: DtoTransaction) {
        super(response);

        // @ts-ignore
        this.api._apiIndex = (Transaction.page.props.auth && Transaction.page.props.auth.user) ? Transaction.endpoints.index : Transaction.endpoints.indexGuest;
        // @ts-ignore
        this.api._apiPost = (Transaction.page.props.auth && Transaction.page.props.auth.user) ? Transaction.endpoints.postAuth : Transaction.endpoints.postGuest;
        this.api._apiPut = Transaction.endpoints.put;
        this.api._apiDelete = Transaction.endpoints.delete;

        this.api.appendWith = ['item', 'user', 'personnel', 'components.item'];

        this.showPage = Transaction.endpoints.show;
    }

    createFields(): object
    {
        return {
            components: [],
            barcode: null,
            barcode_prri: null,
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
            project_code: null,
            par_no: null,
            condition: null,
        };
    }

    updateFields(model: ITransaction): object
    {
        return {
            id: model.id ?? null,
            components: model.components ?? [],
            barcode: model.barcode ?? null,
            barcode_prri: model.barcode_prri ?? null,
            item_id: model.item_id ?? null,
            transac_type: model.transac_type ?? null,
            quantity: model.quantity ?? null,
            unit: model.unit ?? null,
            unit_price: model.unit_price ?? null,
            total_cost: model.total_cost ?? null,
            personnel_id: model.personnel_id ?? null,
            employee_id: model.employee_id ?? null,
            user_id: model.user_id ?? Transaction.currentUserId(),
            expiration: model.expiration ?? null,
            remarks: model.remarks ?? null,
            project_code: model.project_code ?? null,
            par_no: model.par_no ?? null,
            condition: model.condition ?? null,
        };
    }

    deleteField(model: ITransaction): object
    {
        return {
            id: model?.id ?? null,
            barcode: model?.barcode ?? null,
            force: false,
            confirmation_barcode: '',
        };
    }

    get dataColor( ){
        return `${this.transac_type && this.transac_type === 'incoming' ? 'text-green-600' : 'text-red-600'}`;
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
                title: 'Type',
                key: 'transac_type',
                db_key: 'transac_type',
                align: 'flex justify-center',
                sortable: true,
                visible: true,
            },{
                title: 'Item',
                key: 'item.name',
                db_key: 'item',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'Barcode',
                key: 'barcode',
                db_key: 'barcode',
                align: 'text-center justify-center',
                sortable: true,
                visible: true,
            },{
                title: 'PRRI Barcode',
                key: 'barcode_prri',
                db_key: 'barcode_prri',
                align: 'text-center justify-center',
                sortable: true,
                visible: true,
            },{
                title: 'Quantity',
                key: 'quantityWithUnit',
                db_key: 'quantity',
                align: 'text-center justify-center',
                sortable: true,
                visible: true,
            },{
                title: 'Unit',
                key: 'unit',
                db_key: 'unit',
                align: 'text-center',
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
                title: 'Actor',
                key: 'actor_display_name',
                db_key: 'personnel_id',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'Project Code',
                key: 'project_code',
                db_key: 'project_code',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'Components',
                key: 'components.length',
                db_key: 'components',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'Date Created',
                key: 'created_at',
                db_key: 'created_at',
                align: 'flex justify-center',
                sortable: true,
                visible: true,
            },{
                title: 'Remarks',
                key: 'remarks',
                db_key: 'remarks',
                align: 'dataColor',
                sortable: true,
                visible: true,
            },{
                title: 'PAR No',
                key: 'par_no',
                db_key: 'par_no',
                align: 'dataColor',
                sortable: true,
                visible: false,
            },{
                title: 'Condition',
                key: 'condition',
                db_key: 'condition',
                align: 'dataColor',
                sortable: true,
                visible: false,
            }
        ]
    }
}
